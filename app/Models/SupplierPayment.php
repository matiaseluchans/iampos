<?php

namespace App\Models;

use App\Http\Traits\AuditModelTrait;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'purchase_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'reference',
        'notes',
        'tenant_id',
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'datetime:d/m/Y H:i:s',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
