<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository extends  BaseRepository
{
    public function __construct(Customer $m)
    {
        parent::__construct($m);
    }
}
