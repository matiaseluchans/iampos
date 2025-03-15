<?php
namespace App\Repositories;

use App\Models\PersonalPsa;

class PersonalPsaRepository extends  BaseRepository
{    
    public function __construct(PersonalPsa $personalPsa)
    {
        parent::__construct($personalPsa);
    }
}