<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost_price',
        'discount_amount',
        'total_price',
        'total_profit',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);        
    }

    /**
     * Relaciones
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
