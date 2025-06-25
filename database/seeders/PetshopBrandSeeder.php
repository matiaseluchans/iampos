<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopBrandSeeder extends Seeder
{
    public function run()
    {
        DB::table('brands')->insert([
            'id' => 2,
            'name' => 'Pet shop',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
