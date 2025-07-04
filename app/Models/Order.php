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
        'shipping_address',
        'status_id',
        'order_type_id',
        'shipping_status_id',
        'quantity_products',        
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'total_cost',
        'total_profit',
        'payment_status_id',
        'notes',
        'tenant_id',
        'created_by',
        'last_modified_by',
        'deleted_by',                
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

    public function shippingStatus()
    {
        return $this->belongsTo(ShippingStatus::class);
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class);
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

