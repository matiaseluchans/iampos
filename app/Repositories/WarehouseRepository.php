<?php

namespace App\Repositories;

use App\Models\Warehouse;

class WarehouseRepository extends  BaseRepository
{
    public function __construct(Warehouse $m)
    {
        parent::__construct($m);
    }
}
