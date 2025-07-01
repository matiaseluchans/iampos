<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;


class Stock  extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;


    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
        'maximum_stock',
        'tenant_id',
        'created_by'
    ];

    protected $casts = [
        //'quantity' => 'decimal:2',
        //'reserved_quantity' => 'decimal:2',
        //'minimum_stock' => 'decimal:2'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con el almacén
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con los movimientos de stock
     */
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Stock disponible (cantidad - reservado)
     */
    public function getAvailableAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Verifica si el stock está por debajo del mínimo
     */
    public function getBelowMinimumAttribute()
    {
        return $this->available < $this->minimum_stock;
    }

    /**
     * Scope para buscar por producto
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para buscar por almacén
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope para productos con stock bajo
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity - reserved_quantity < minimum_stock');
    }

    /**
     * Registrar un movimiento de stock
     */
    public function recordMovement($type, $quantity, $notes = null, $sourceDocument = null)
    {
        return $this->movements()->create([
            'movement_type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $this->quantity,
            'new_quantity' => $this->quantity + $quantity,
            'source_document_type' => $sourceDocument ? get_class($sourceDocument) : null,
            'source_document_id' => $sourceDocument ? $sourceDocument->id : null,
            'notes' => $notes,
            'tenant_id' => $this->tenant_id,
            'user_id' => auth()->id() ?? null
        ]);
    }


    public static function createOrUpdate($productId, $warehouseId, $quantity, $tenantId)
    {
        return static::updateOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'tenant_id' => $tenantId
            ],
            [
                'quantity' => $quantity,
                'created_by' => auth()->id()
            ]
        );
    }

    /**
     * Reservar stock
     */
    public function reserve($quantity)
    {
        if ($this->available < $quantity) {
            throw new \Exception('Stock insuficiente para reservar');
        }

        $this->reserved_quantity += $quantity;
        $this->save();

        return $this;
    }

    /**
     * Liberar stock reservado
     */
    public function releaseReservation($quantity)
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        $this->save();

        return $this;
    }

    /**
     * Verificar si hay suficiente stock disponible
     */
    public function hasAvailableStock($quantity)
    {
        return $this->available >= $quantity;
    }
}
