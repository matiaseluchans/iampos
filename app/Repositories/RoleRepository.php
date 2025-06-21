<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends  BaseRepository
{
    public function __construct(Role $m)
    {
        parent::__construct($m);
    }
}
