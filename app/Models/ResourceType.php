<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class ResourceType extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'name',
        'description',
        'is_shared_capacity',
        'max_capacity_per_reservation',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'is_shared_capacity' => 'boolean'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function serviceTypes()
    {
        return $this->hasMany(ServiceType::class);
    }

    public function availableResourcesCount($startTime, $endTime, $filters = [])
    {
        $count = 0;
        
        foreach ($this->resources as $resource) {
            if ($resource->is_shared) {
                $availableCapacity = $resource->getAvailableCapacity($startTime, $endTime);
                if ($availableCapacity > 0) {
                    $count += $availableCapacity;
                }
            } else {
                if ($resource->availability($startTime, $endTime)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}