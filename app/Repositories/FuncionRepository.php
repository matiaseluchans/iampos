<?php

namespace App\Repositories;

use App\Models\Funcion;

class FuncionRepository extends  BaseRepository
{    
    public function __construct(Funcion $funcion)
    {
        parent::__construct($funcion);
    }

}