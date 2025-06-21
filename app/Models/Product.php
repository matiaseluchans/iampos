<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Product extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'category_id',
        'brand_id',
        'code',
        'image',
        'purchase_price',
        'sale_price',
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


    // Relación con categoría
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relación con marca
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }


    // Accesor para la URL de la imagen
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/default-product.png');
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/' . $this->image);
    }

    // Scope para productos activos
    public function scopeActives($query)
    {
        return $query->where('active', true);
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%");
    }

    // Método para calcular el margen de ganancia
    public function revenue()
    {
        if (!$this->purchase_price || !$this->sale_price) {
            return 0;
        }

        return $this->sale_price - $this->purchase_price;
    }

    // Método para calcular el porcentaje de ganancia
    public function revenuePercentage()
    {
        if (!$this->purchase_price || $this->purchase_price == 0) {
            return 0;
        }

        return (($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100;
    }
}
