<?php

namespace App\Repositories;

use App\Models\PriceList;

class PriceListRepository extends BaseRepository
{
    public function __construct(PriceList $m)
    {
        parent::__construct($m);
    }
}
