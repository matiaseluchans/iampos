<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'order_date',
        'delivery_date',
        'order_number',
        'customer_id',
        'customer_details',
        'shipping',
        'shipping_address',
        'status_id',
        'order_type_id',
        'quantity_products',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'total_cost',
        'total_profit',
        'notes',
        'seller_id',
        'seller_name',
        'tenant_id',
        'created_by',
        'last_modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'order_date' => 'datetime:d/m/Y H:i:s',
        'delivery_date' => 'datetime:d/m/Y H:i:s',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'deleted_at' => 'datetime:d/m/Y H:i:s',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber($order->tenant_id);
            }
        });
    }

    public static function generateOrderNumber($tenantId)
    {

        // Obtener el último número para el tenant
        $lastOrder = self::where('tenant_id', $tenantId)
            ->orderBy('order_number', 'desc')
            ->lockForUpdate() // evita conflictos en concurrencia
            ->first();
        //dd($lastOrder);
        // Si no hay órdenes previas, empezamos en 1
        $nextNumber = $lastOrder
            ? intval($lastOrder->order_number) + 1
            : 1;

        // Convertir el número a string de 10 dígitos con ceros a la izquierda
        return str_pad($nextNumber, 10, '0', STR_PAD_LEFT);
    }

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function orderType()
    {
        return $this->belongsTo(OrderType::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
}
