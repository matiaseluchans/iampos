<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth as Auth;

trait AuditModelTrait
{
    public static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->tenant_id = Auth::user()->tenant_id;
            $model->created_by = Auth::user()->id;
        });

        static::updating(function (Model $model) {
            $model->tenant_id = Auth::user()->tenant_id;
            $model->last_modified_by = Auth::user()->id;
        });

        static::deleting(function (Model $model) {
            $model->tenant_id = Auth::user()->tenant_id;
            $model->deleted_by = Auth::user()->id;
            $model->save();
        });
    }
}
