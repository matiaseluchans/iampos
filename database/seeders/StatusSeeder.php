<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run()
    {        
        DB::table('status')->insert([
            'id' => 1,
            'name' => 'Pendiente',
            'active' => true,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('status')->insert([
            'id' => 2,
            'name' => 'Confirmado',
            'active' => true,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('status')->insert([
            'id' => 3,
            'name' => 'Procesando',
            'active' => true,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('status')->insert([
            'id' => 4,
            'name' => 'Completo',
            'active' => true,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('status')->insert([
            'id' => 5,
            'name' => 'Cancelado',
            'active' => true,
            'tenant_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
