<?php

namespace App\Repositories;

use App\Models\Clasificacion;

class ClasificacionRepository extends  BaseRepository
{    
    public function __construct(Clasificacion $clasificacion)
    {
        parent::__construct($clasificacion);
    }

}