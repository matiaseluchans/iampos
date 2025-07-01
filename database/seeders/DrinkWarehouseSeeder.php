<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkWarehouseSeeder extends Seeder
{
    public function run()
    {
        DB::table('warehouses')->insert([
            'id' => 1,
            'name' => 'Deposito',
            'active' => true,
            'tenant_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
