<?php

namespace Database\Seeders;

//use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopServiceTypeSeeder extends Seeder
{
    public function run()
    {
        // Servicios principales
        DB::table('service_types')->insert([
            'name' => 'Baño y Secado Básico',
            'description' => 'Baño completo con shampoo especial para perros, secado y cepillado básico',
            'base_price' => 25.00,
            'duration_minutes' => 60,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 2,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Corte de Pelo Completo',
            'description' => 'Corte de pelo completo según raza o estilo solicitado, incluye baño y secado',
            'base_price' => 45.00,
            'duration_minutes' => 90,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 3,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Limpieza Dental',
            'description' => 'Limpieza dental profesional con productos especializados para perros',
            'base_price' => 35.00,
            'duration_minutes' => 45,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 2,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Spa Canino Premium',
            'description' => 'Servicio completo: baño con aromaterapia, masaje, corte de uñas, limpieza de oídos y cepillado premium',
            'base_price' => 65.00,
            'duration_minutes' => 120,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 2,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Corte de Uñas',
            'description' => 'Corte profesional de uñas y limado',
            'base_price' => 12.00,
            'duration_minutes' => 20,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 1,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Limpieza de Oídos',
            'description' => 'Limpieza profunda de oídos con productos especializados',
            'base_price' => 15.00,
            'duration_minutes' => 15,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 1,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Servicios de peluquería canina creados exitosamente');
    }
}