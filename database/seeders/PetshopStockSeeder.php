<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopStockSeeder extends Seeder
{
    public function run()
    {
        for ($productId = 261; $productId <= 1260; $productId++) {
            DB::table("stocks")->insert([
                'product_id'        => $productId,
                'warehouse_id'      => null,
                'quantity'          => rand(4, 10),
                'reserved_quantity' => 0,
                'minimum_stock'     => rand(4, 10),
                'maximum_stock'     => rand(4, 10),
                'tenant_id'         => 3,
                'created_by'        => 'Seeder',
                'last_modified_by'  => null,
                'deleted_by'        => null,
            ]);
        }
    }
}
