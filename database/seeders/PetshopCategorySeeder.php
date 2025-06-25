<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->insert([
            'id' => 2,
            'name' => 'Pet Shop',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
