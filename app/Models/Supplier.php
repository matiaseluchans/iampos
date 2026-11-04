<?php

namespace App\Models;

use App\Http\Traits\AuditModelTrait;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'name',
        'business_name',
        'tax_id',
        'contact_name',
        'email',
        'telephone',
        'address',
        'locality_id',
        'payment_term_days',
        'notes',
        'active',
        'tenant_id',
        'created_by',
        'last_modified_by',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
