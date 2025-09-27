<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class ServiceType extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'duration_minutes',
        'time_unit',
        'min_units',
        'max_units',
        'max_participants',
        'requires_resource',
        'resource_type_id',
        'requires_capacity',
        'max_capacity_per_reservation',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'requires_resource' => 'boolean',
        'requires_capacity' => 'boolean',
        'min_units' => 'integer',
        'max_units' => 'integer',
        'max_participants' => 'integer',
        'max_capacity_per_reservation' => 'integer',
        'base_price' => 'decimal:2'
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

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }

    public function availableResourcesCount($startTime, $endTime, $filters = [])
    {
        if (!$this->requires_resource) {
            return null;
        }
        
        return $this->resourceType->availableResourcesCount($startTime, $endTime, $filters);
    }

    public function calculateEndTime($startTime, $timeUnits)
    {
        $timeService = app(\App\Services\TimeCalculationService::class);
        return $timeService->calculateEndTime($this, $startTime, $timeUnits);
    }

    public function validateTimeUnits($timeUnits)
    {
        $timeService = app(\App\Services\TimeCalculationService::class);
        return $timeService->validateTimeUnits($this, $timeUnits);
    }
}