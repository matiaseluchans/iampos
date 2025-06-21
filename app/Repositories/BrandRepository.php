<?php

namespace App\Repositories;

use App\Models\Brand;

class BrandRepository extends  BaseRepository
{
    public function __construct(Brand $m)
    {
        parent::__construct($m);
    }
}
