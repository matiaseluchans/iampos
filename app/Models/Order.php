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
        'tenant_id',
        'created_by',
        'last_modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'order_date' => 'datetime:d/m/Y H:i:s',
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
        'deleted_at' => 'datetime:d/m/Y H:i:s',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $random = strtoupper(Str::random(6));
        $timestamp = now()->format('YmdHis');

        return $prefix . $timestamp . '-' . $random;
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
}
