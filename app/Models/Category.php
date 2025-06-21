<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;
use App\Http\Traits\AuditModelTrait;

class Category extends Model
{
    use HasFactory, SoftDeletes, AuditModelTrait;

    protected $table = 'categories';
    protected $fillable = [


        'name',
        'category_id',
        'active',
        'created_by',
        'last_modified_by',
        'deleted_by',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoryParents()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categoryChildrens()
    {
        return $this->hasMany(Category::class, 'category_id');
    }
}
