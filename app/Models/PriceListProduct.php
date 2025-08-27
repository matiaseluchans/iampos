<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class PriceListProduct extends Model
{
    use HasFactory;

    protected $table = 'price_list_product';

    protected $fillable = [
        'price_list_id',
        'product_id',
        'sale_price',
        'tenant_id',
        'created_by',
        'last_modified_by'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    // Relación con la lista de precios
    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
