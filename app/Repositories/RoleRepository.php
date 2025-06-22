<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends  BaseRepository
{
    public function __construct(Role $m)
    {
        parent::__construct($m);
    }

    public function getByTenant($tenantId)
    {
        try {
            $query = $this->model->where('tenant_id', $tenantId);

            if (!empty($this->relations)) {
                $query = $query->with($this->relations);
            }

            return $this->successResponse($query->get());
        } catch (\Exception $e) {
            report($e);
            return $this->errorResponse($e);
        }
    }
}
