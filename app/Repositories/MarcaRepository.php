<?php

namespace App\Repositories;

use App\Models\Marca;

class MarcaRepository extends  BaseRepository
{
    public function __construct(Marca $m)
    {
        parent::__construct($m);
    }
}
