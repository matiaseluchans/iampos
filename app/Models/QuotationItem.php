<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class QuotationItem extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $fillable = [
        'quotation_id',
        'description',
        'price',
        'quantity',
        'notes',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }    

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}