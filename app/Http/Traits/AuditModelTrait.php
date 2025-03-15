<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

trait AuditModelTrait
{
    public static function boot()
    {
        parent::boot();        

        static::creating(function (Model $model) {            
            $model->created_by = 'usuario creador';            
        });

        static::updating(function (Model $model) {
            $model->last_modified_by = 'usuario modificador';
        });

        static::deleting(function (Model $model) {
            $model->deleted_by = 'usuario eliminador';
            $model->save();
        });
    }
}