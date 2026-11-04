<?php

namespace App\Models;

use App\Http\Traits\AuditModelTrait;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'purchase_date',
        'purchase_number',
        'supplier_id',
        'warehouse_id',
        'supplier_document',
        'due_date',
        'status',
        'payment_status',
        'quantity_products',
        'subtotal',
        'discount_amount',
        'aditional',
        'total_amount',
        'total_paid',
        'notes',
        'cancelled_at',
        'cancel_reason',
        'tenant_id',
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'purchase_date' => 'datetime:d/m/Y H:i:s',
        'due_date' => 'date:Y-m-d',
        'cancelled_at' => 'datetime:d/m/Y H:i:s',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'aditional' => 'float',
        'total_amount' => 'float',
        'total_paid' => 'float',
    ];

    protected $appends = ['balance'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $purchase->purchase_number = self::generateNumber(auth()->user()->tenant_id);
            }
        });
    }

    /**
     * Número correlativo por tenant (10 dígitos, igual que las órdenes).
     * Debe llamarse dentro de una transacción para que lockForUpdate sea efectivo.
     */
    public static function generateNumber($tenantId): string
    {
        $last = self::withoutGlobalScopes()->withTrashed()
            ->where('tenant_id', $tenantId)
            ->orderBy('purchase_number', 'desc')
            ->lockForUpdate()
            ->first();

        $next = $last ? intval($last->purchase_number) + 1 : 1;

        return str_pad($next, 10, '0', STR_PAD_LEFT);
    }

    public function getBalanceAttribute(): float
    {
        return round(($this->total_amount ?? 0) - ($this->total_paid ?? 0), 2);
    }

    /**
     * Recalcula total_paid y payment_status a partir de los pagos activos.
     */
    public function refreshPaymentStatus(): void
    {
        $paid = (float) $this->payments()->lockForUpdate()->sum('amount');

        $this->total_paid = round($paid, 2);
        if ($this->total_amount > 0 && $paid >= (float) $this->total_amount) {
            $this->payment_status = self::PAYMENT_PAID;
        } elseif ($paid > 0) {
            $this->payment_status = self::PAYMENT_PARTIAL;
        } else {
            $this->payment_status = self::PAYMENT_PENDING;
        }
        $this->save();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
