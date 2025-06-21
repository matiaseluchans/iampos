<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->insert([
            'id' => 1,
            'name' => 'Bebidas',
            'active' => true,
            'tenant_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
