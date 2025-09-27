<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class PricingRule extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'service_type_id',
        'name',
        'description',
        'conditions',
        'modification_type',
        'modification_value',
        'priority',
        'is_active',
        'valid_from',
        'valid_until',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
        'modification_value' => 'decimal:2',
        'priority' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime'
    ];

    const MODIFICATION_FIXED_AMOUNT = 'fixed_amount';
    const MODIFICATION_PERCENTAGE = 'percentage';
    const MODIFICATION_FIXED_RATE = 'fixed_rate';

    public static function getModificationTypes()
    {
        return [
            self::MODIFICATION_FIXED_AMOUNT,
            self::MODIFICATION_PERCENTAGE,
            self::MODIFICATION_FIXED_RATE
        ];
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('valid_from')
                              ->orWhere('valid_from', '<=', now());
                    })
                    ->where(function($query) {
                        $query->whereNull('valid_until')
                              ->orWhere('valid_until', '>=', now());
                    });
    }

    public function scopeValidForDate($query, $date)
    {
        return $query->where('is_active', true)
                    ->where(function($query) use ($date) {
                        $query->whereNull('valid_from')
                              ->orWhere('valid_from', '<=', $date);
                    })
                    ->where(function($query) use ($date) {
                        $query->whereNull('valid_until')
                              ->orWhere('valid_until', '>=', $date);
                    });
    }

    public function isCurrentlyValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}