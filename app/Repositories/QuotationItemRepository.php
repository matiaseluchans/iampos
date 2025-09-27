<?php

namespace App\Repositories;

use App\Models\QuotationItem;

class QuotationItemRepository extends BaseRepository
{
    public function __construct(QuotationItem $m)
    {
        parent::__construct($m);
    }

}