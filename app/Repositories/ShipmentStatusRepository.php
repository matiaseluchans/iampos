<?php

namespace App\Repositories;

use App\Models\ShipmentStatus;

class ShipmentStatusRepository extends BaseRepository
{
    public function __construct(ShipmentStatus $m)
    {
        parent::__construct($m);
    }
}
