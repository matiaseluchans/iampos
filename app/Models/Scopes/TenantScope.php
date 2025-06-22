<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{

    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        // Si no hay usuario o es superadmin, no aplicamos el filtro
        if (!$user || ($user->tenant && $user->tenant->id === 1)) {
            return;
        }

        // Usuario común: filtramos por tenant_id
        $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
    }
}
