<?php

namespace Database\Seeders;

use App\Models\ResourceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('resource_types')->insert([
            'name' => 'Espacio de Parking',
            'description' => 'Áreas de estacionamiento para vehículos',
            'is_shared_capacity' => true,
            'max_capacity_per_reservation' => 10,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resource_types')->insert([
            'name' => 'Habitación de Hotel',
            'description' => 'Diferentes tipos de habitaciones hoteleras',
            'is_shared_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resource_types')->insert([
            'name' => 'Consultorio Médico',
            'description' => 'Consultorios equipados para atención médica',
            'is_shared_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resource_types')->insert([
            'name' => 'Sala de Espera',
            'description' => 'Áreas de espera compartidas',
            'is_shared_capacity' => true,
            'max_capacity_per_reservation' => 20,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Tipos de recursos creados: Parking, Hotel, Consultorio Médico');
    }
}