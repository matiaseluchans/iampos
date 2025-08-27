<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class PriceList extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $table = 'price_lists';

    protected $fillable = [
        'name',
        'description',
        'is_default',
        'active',
        'tenant_id',
        'created_by',
        'last_modified_by'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    // Relación con tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Relación de muchos a muchos con productos
    public function products()
    {
        return $this->belongsToMany(Product::class, 'price_list_product')
            ->withPivot('sale_price')
            ->withTimestamps();
    }
}
