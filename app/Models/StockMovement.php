<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;


class StockMovement  extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;


    protected $fillable = [
        'stock_id',
        'movement_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'notes',
        'tenant_id',
        'user_id'
    ];

    protected $casts = [
        /*'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',*/];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    /**
     * Tipos de movimiento disponibles
     */
    public static $movementTypes = [
        'entry' => 'Entrada',
        'exit' => 'Salida',
        'adjustment' => 'Ajuste',
        'transfer' => 'Transferencia'
    ];

    /**
     * Relación con el stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relación con el usuario que realizó el movimiento
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación polimórfica con el documento origen
     */
    public function sourceDocument()
    {
        return $this->morphTo();
    }

    /**
     * Obtener el tipo de movimiento formateado
     */
    public function getFormattedTypeAttribute()
    {
        return self::$movementTypes[$this->movement_type] ?? $this->movement_type;
    }

    /**
     * Scope para movimientos de entrada
     */
    public function scopeEntries($query)
    {
        return $query->where('movement_type', 'entry');
    }

    /**
     * Scope para movimientos de salida
     */
    public function scopeExits($query)
    {
        return $query->where('movement_type', 'exit');
    }

    /**
     * Scope para movimientos de un producto específico
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('stock', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }
}
