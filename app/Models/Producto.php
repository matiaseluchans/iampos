<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Producto extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'productos_categorias_id',
        'marca_id',
        'codigo',
        'image',
        'precio_compra',
        'precio_venta',
        'activo',
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
    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'productos_categorias_id');
    }

    // Relación con marca
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
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
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para búsqueda
    public function scopeBuscar($query, $search)
    {
        return $query->where('nombre', 'like', "%{$search}%")
            ->orWhere('codigo', 'like', "%{$search}%");
    }

    // Método para calcular el margen de ganancia
    public function margenGanancia()
    {
        if (!$this->precio_compra || !$this->precio_venta) {
            return 0;
        }

        return $this->precio_venta - $this->precio_compra;
    }

    // Método para calcular el porcentaje de ganancia
    public function porcentajeGanancia()
    {
        if (!$this->precio_compra || $this->precio_compra == 0) {
            return 0;
        }

        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }
}
