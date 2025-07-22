<?php

namespace App\Repositories;

use App\Models\PaymentStatus;

class PaymentStatusRepository extends BaseRepository
{
    public function __construct(PaymentStatus $m)
    {
        parent::__construct($m);
    }
}
