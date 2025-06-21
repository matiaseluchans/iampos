<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends  BaseRepository
{
    public function __construct(Product $m)
    {
        parent::__construct($m);
    }
}
