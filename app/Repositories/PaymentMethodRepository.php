<?php

namespace App\Repositories;

use App\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository
{
    public function __construct(PaymentMethod $m)
    {
        parent::__construct($m);
    }
}
