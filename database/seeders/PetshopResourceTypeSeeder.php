<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopResourceTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('resource_types')->insert([
            'name' => 'Estación de Peluquería Canina',
            'description' => 'Puestos de trabajo especializados para peluquería canina',
            'is_shared_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('resource_types')->insert(
        [
            'name' => 'Sala de Espera Canina',
            'description' => 'Área de espera para perros antes y después del servicio',
            'is_shared_capacity' => true,
            'max_capacity_per_reservation' => 10,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('resource_types')->insert(
        [
            'name' => 'Área de Secado',
            'description' => 'Zona especializada para secado de perros',
            'is_shared_capacity' => true,
            'max_capacity_per_reservation' => 3,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Tipos de recursos para peluquería canina creados exitosamente');
    }
}