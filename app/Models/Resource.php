<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Resource extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'name',
        'description',
        'resource_type_id',
        'capacity',
        'current_usage',
        'is_shared',
        'features',
        'is_active',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'capacity' => 'integer',
        'current_usage' => 'integer'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function availability($startTime, $endTime, $requiredCapacity = 1)
    {
        if (!$this->is_shared) {
            return !$this->hasConflictingReservations($startTime, $endTime);
        }
        
        return $this->getAvailableCapacity($startTime, $endTime) >= $requiredCapacity;
    }

    public function getAvailableCapacity($startTime, $endTime)
    {
        // Para recursos no compartidos, verificar solapamiento de tiempo
        if (!$this->is_shared) {
            return $this->hasConflictingReservations($startTime, $endTime) ? 0 : 1;
        }

        // Para recursos compartidos, verificar capacidad disponible
        $reservedCapacity = $this->reservations()
            ->where('status', 'confirmed')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function($query) use ($startTime, $endTime) {
                          $query->where('start_time', '<', $startTime)
                                ->where('end_time', '>', $endTime);
                      });
            })
            ->sum('required_capacity');

        return max(0, $this->capacity - $reservedCapacity);
    }

    protected function hasConflictingReservations($startTime, $endTime)
    {
        return $this->reservations()
            ->where('status', 'confirmed')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function($query) use ($startTime, $endTime) {
                          $query->where('start_time', '<', $startTime)
                                ->where('end_time', '>', $endTime);
                      });
            })
            ->exists();
    }

    public function updateCurrentUsage()
    {
        if ($this->is_shared) {
            $reservedCapacity = $this->reservations()
                ->where('status', 'confirmed')
                ->where('end_time', '>', now())
                ->sum('required_capacity');
            
            $this->current_usage = $reservedCapacity;
            $this->save();
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    public function scopeExclusive($query)
    {
        return $query->where('is_shared', false);
    }
}