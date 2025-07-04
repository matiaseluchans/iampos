<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Customer extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [

        'address',
        'locality_id',
        'telephone',
        'email',
        'firstname',
        'lastname',
        'companyname',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
        'active',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
