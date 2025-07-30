<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository extends  BaseRepository
{
    public function __construct(Customer $m, array $relations = ['locality'])
    {
        parent::__construct($m, $relations);
    }
}
