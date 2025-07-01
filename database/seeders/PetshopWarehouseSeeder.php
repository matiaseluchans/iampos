<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopWarehouseSeeder extends Seeder
{
    public function run()
    {
        DB::table('warehouses')->insert([
            'id' => 2,
            'name' => 'Deposito',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
