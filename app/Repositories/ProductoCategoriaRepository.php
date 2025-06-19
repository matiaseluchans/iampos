<?php

namespace App\Repositories;

use App\Models\ProductoCategoria;

class ProductoCategoriaRepository extends  BaseRepository
{
    public function __construct(ProductoCategoria $m)
    {
        parent::__construct($m);
    }
}
