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

    protected $fillable = [

        'productos_categorias_id',
        'nombre',
        'codigo',
        'image',
        'precio_compra',
        'precio_venta',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
        'activo',
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
