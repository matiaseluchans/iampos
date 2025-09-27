<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'reservation_id',
        'base_price',
        'tax_amount',
        'discount_amount',
        'total_price',
        'valid_until',
        'status',
        'notes',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'valid_until' => 'datetime'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_EXPIRED
        ];
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function scopeValid($query)
    {
        return $query->where('valid_until', '>', now())
                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<=', now())
                    ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function accept()
    {
        $this->update(['status' => self::STATUS_ACCEPTED]);
        $this->reservation->confirm();
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'notes' => $reason
        ]);
        $this->reservation->cancel('Cotización rechazada: ' . $reason);
    }

    public function markAsSent()
    {
        $this->update(['status' => self::STATUS_SENT]);
    }

    public function isExpired()
    {
        return $this->valid_until->isPast() && 
               in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    public function calculateTotal()
    {
        $subtotal = $this->items->sum('price');
        $total = $subtotal + $this->tax_amount - $this->discount_amount;
        
        $this->update(['total_price' => $total]);
        return $total;
    }

    public function addItem($description, $price, $quantity = 1)
    {
        return $this->items()->create([
            'description' => $description,
            'price' => $price,
            'quantity' => $quantity
        ]);
    }
}