<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run()
    {
        // ==================== PARKING ====================
        DB::table('service_types')->insert([
            'name' => 'Estacionamiento Diurno',
            'description' => 'Estacionamiento por horas durante el día (6:00 - 20:00)',
            'base_price' => 3.50,
            'duration_minutes' => 60, // Precio por hora
            'time_unit' => 'hours',
            'min_units' => 1,
            'max_units' => 14, // Máximo 14 horas (6:00-20:00)
            'max_participants' => null,
            'requires_resource' => true,
            'resource_type_id' => 1, // Espacio de Parking
            'requires_capacity' => true,
            'max_capacity_per_reservation' => 5,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Estacionamiento Nocturno',
            'description' => 'Estacionamiento durante la noche (20:00 - 6:00)',
            'base_price' => 12.00,
            'duration_minutes' => 60,
            'time_unit' => 'hours',
            'min_units' => 1,
            'max_units' => 10, // Máximo 10 horas nocturnas
            'max_participants' => null,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => true,
            'max_capacity_per_reservation' => 5,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Estacionamiento por Día Completo',
            'description' => 'Estacionamiento por 24 horas continuas',
            'base_price' => 25.00,
            'duration_minutes' => 1440, // 24 horas
            'time_unit' => 'days',
            'min_units' => 1,
            'max_units' => 30, // Máximo 30 días
            'max_participants' => null,
            'requires_resource' => true,
            'resource_type_id' => 1,
            'requires_capacity' => true,
            'max_capacity_per_reservation' => 5,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================== HOTELERÍA ====================
        DB::table('service_types')->insert([
            'name' => 'Habitación Individual - 1 Noche',
            'description' => 'Habitación individual con cama individual, baño privado',
            'base_price' => 89.00,
            'duration_minutes' => 1440, // 24 horas (1 noche)
            'time_unit' => 'days',
            'min_units' => 1,
            'max_units' => 30, // Máximo 30 noches
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 2, // Habitación de Hotel
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Habitación Doble - 1 Noche',
            'description' => 'Habitación doble con cama queen size, baño privado',
            'base_price' => 129.00,
            'duration_minutes' => 1440,
            'time_unit' => 'days',
            'min_units' => 1,
            'max_units' => 30,
            'max_participants' => 2,
            'requires_resource' => true,
            'resource_type_id' => 2,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Suite Ejecutiva - 1 Noche',
            'description' => 'Suite de lujo con sala de estar y amenities premium',
            'base_price' => 199.00,
            'duration_minutes' => 1440,
            'time_unit' => 'days',
            'min_units' => 1,
            'max_units' => 30,
            'max_participants' => 2,
            'requires_resource' => true,
            'resource_type_id' => 2,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Habitación Familiar - 1 Noche',
            'description' => 'Habitación familiar con capacidad para 4 personas',
            'base_price' => 169.00,
            'duration_minutes' => 1440,
            'time_unit' => 'days',
            'min_units' => 1,
            'max_units' => 30,
            'max_participants' => 4,
            'requires_resource' => true,
            'resource_type_id' => 2,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================== CONSULTORIO MÉDICO ====================
        DB::table('service_types')->insert([
            'name' => 'Consulta Médica General',
            'description' => 'Consulta con médico general, duración 30 minutos',
            'base_price' => 50.00,
            'duration_minutes' => 30,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 2, // Máximo 1 hora
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 3, // Consultorio Médico
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Consulta con Especialista',
            'description' => 'Consulta con médico especialista, duración 45 minutos',
            'base_price' => 80.00,
            'duration_minutes' => 45,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 2,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 3,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Control de Rutina',
            'description' => 'Control médico de rutina, duración 20 minutos',
            'base_price' => 35.00,
            'duration_minutes' => 20,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 1,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 3,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_types')->insert([
            'name' => 'Examen Médico Completo',
            'description' => 'Examen médico completo con estudios, duración 90 minutos',
            'base_price' => 120.00,
            'duration_minutes' => 90,
            'time_unit' => 'minutes',
            'min_units' => 1,
            'max_units' => 1,
            'max_participants' => 1,
            'requires_resource' => true,
            'resource_type_id' => 3,
            'requires_capacity' => false,
            'max_capacity_per_reservation' => 1,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Servicios creados: Parking (3), Hotel (4), Consultorio Médico (4)');
    }
}