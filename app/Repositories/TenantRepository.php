<?php

namespace App\Repositories;

use App\Models\Tenant;

class TenantRepository extends  BaseRepository
{
    public function __construct(Tenant $m)
    {
        parent::__construct($m);
    }
}
