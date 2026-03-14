<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class ProductImportLog extends Model
{
    use HasFactory;

    protected $table = 'product_imports';

    protected $fillable = [
        'file_name',
        'file_size',
        'status',
        'total_rows',
        'processed_rows',
        'error_message',
        'user_id',
        'tenant_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
