<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Payment extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'order_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'reference',
        'notes',
        'tenant_id',
        'reservation_id',
        'quotation_id',
        'transaction_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    
    /*
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'payment_date' => now(),
            'transaction_id' => $transactionId
        ]);
    }

    public function markAsFailed($notes = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $notes
        ]);
    }

    public function refund($notes = null)
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'notes' => $notes
        ]);
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }*/

    // Relaciones

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

