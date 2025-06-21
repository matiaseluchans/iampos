<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkBrandSeeder extends Seeder
{
    public function run()
    {
        DB::table('brands')->insert([
            'id' => 1,
            'name' => 'Coca Cola',
            'active' => true,
            'tenant_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
