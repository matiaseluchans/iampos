<?php

namespace App\Repositories;

use App\Models\Producto;

class ProductoRepository extends  BaseRepository
{
    public function __construct(Producto $m)
    {
        parent::__construct($m);
    }
}
