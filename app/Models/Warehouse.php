<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;


class Warehouse extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'location',
        'active',
        'tenant_id',
        'created_by',
        'last_modified_by',
        'deleted_by',
    ];



    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con los stocks en este almacén
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Relación con los usuarios asignados a este almacén (si es necesario)
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['role', 'is_manager']);
    }

    /**
     * Scope para filtrar almacenes activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para buscar por código o nombre
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%");
    }

    /**
     * Obtener la dirección completa formateada
     */
    public function getFullLocationAttribute()
    {
        return "{$this->name} - {$this->location}";
    }

    /**
     * Método para activar/desactivar el almacén
     */
    public function toggleStatus()
    {
        $this->active = !$this->active;
        $this->save();
        return $this;
    }
}
