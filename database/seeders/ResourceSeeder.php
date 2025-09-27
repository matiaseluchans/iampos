<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceSeeder extends Seeder
{
    public function run()
    {
        // ==================== PARKING - 100 LUGARES ====================
        DB::table('resources')->insert([
            'name' => 'Parking Principal - Nivel 1',
            'description' => 'Estacionamiento principal cubierto, nivel 1',
            'resource_type_id' => 4, // Espacio de Parking
            'capacity' => 40,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'cubierto' => true,
                'seguridad_24h' => true,
                'iluminacion_led' => true,
                'acceso_controlado' => true,
                'camaras_vigilancia' => true,
                'tipo_vehiculo' => ['car', 'suv'],
                'altura_maxima' => '2.2m'
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resources')->insert([
            'name' => 'Parking Principal - Nivel 2',
            'description' => 'Estacionamiento principal cubierto, nivel 2',
            'resource_type_id' => 4,
            'capacity' => 40,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'cubierto' => true,
                'seguridad_24h' => true,
                'iluminacion_led' => true,
                'acceso_controlado' => true,
                'camaras_vigilancia' => true,
                'tipo_vehiculo' => ['car', 'suv'],
                'altura_maxima' => '2.2m'
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resources')->insert([
            'name' => 'Parking Exterior',
            'description' => 'Estacionamiento descubierto',
            'resource_type_id' => 4,
            'capacity' => 20,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'cubierto' => false,
                'seguridad_24h' => true,
                'iluminacion_nocturna' => true,
                'acceso_directo' => true,
                'camaras_vigilancia' => true,
                'tipo_vehiculo' => ['car', 'suv', 'van'],
                'altura_maxima' => '3.5m'
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================== HOTEL - 10 HABITACIONES ====================
        // Habitaciones Individuales
        for ($i = 1; $i <= 3; $i++) {
            DB::table('resources')->insert([
                'name' => "Habitación Individual {$i}",
                'description' => "Habitación individual con vista al jardín",
                'resource_type_id' => 5, // Habitación de Hotel
                'capacity' => 1,
                'current_usage' => 0,
                'is_shared' => false,
                'features' => json_encode([
                    'tipo' => 'individual',
                    'cama' => 'individual',
                    'vista' => 'jardin',
                    'wifi' => true,
                    'tv' => true,
                    'ac' => true,
                    'baño_privado' => true,
                    'superficie' => '20m²'
                ]),
                'is_active' => true,
                'tenant_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Habitaciones Dobles
        for ($i = 1; $i <= 4; $i++) {
            DB::table('resources')->insert([
                'name' => "Habitación Doble {$i}",
                'description' => "Habitación doble con cama queen size",
                'resource_type_id' => 5,
                'capacity' => 2,
                'current_usage' => 0,
                'is_shared' => false,
                'features' => json_encode([
                    'tipo' => 'doble',
                    'cama' => 'queen_size',
                    'vista' => $i <= 2 ? 'ciudad' : 'jardin',
                    'wifi' => true,
                    'tv' => true,
                    'ac' => true,
                    'baño_privado' => true,
                    'minibar' => true,
                    'superficie' => '30m²'
                ]),
                'is_active' => true,
                'tenant_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Suites
        for ($i = 1; $i <= 2; $i++) {
            DB::table('resources')->insert([
                'name' => "Suite Ejecutiva {$i}",
                'description' => "Suite de lujo con sala de estar",
                'resource_type_id' => 5,
                'capacity' => 2,
                'current_usage' => 0,
                'is_shared' => false,
                'features' => json_encode([
                    'tipo' => 'suite',
                    'cama' => 'king_size',
                    'vista' => 'panoramica',
                    'wifi' => true,
                    'tv_plana' => true,
                    'ac' => true,
                    'baño_privado' => true,
                    'minibar' => true,
                    'sala_estar' => true,
                    'jacuzzi' => true,
                    'superficie' => '50m²'
                ]),
                'is_active' => true,
                'tenant_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Habitación Familiar
        DB::table('resources')->insert([
            'name' => "Habitación Familiar",
            'description' => "Habitación familiar con capacidad para 4 personas",
            'resource_type_id' => 5,
            'capacity' => 4,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'tipo' => 'familiar',
                'camas' => ['queen_size', 'individual_x2'],
                'vista' => 'jardin',
                'wifi' => true,
                'tv' => true,
                'ac' => true,
                'baño_privado' => true,
                'minibar' => true,
                'superficie' => '40m²'
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================== CONSULTORIOS MÉDICOS ====================
        DB::table('resources')->insert([
            'name' => "Consultorio 1 - General",
            'description' => "Consultorio para atención médica general",
            'resource_type_id' => 6, // Consultorio Médico
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'especialidad' => 'general',
                'equipamiento' => ['estetoscopio', 'tensiometro', 'termometro'],
                'camilla' => true,
                'computadora' => true,
                'privacidad' => true,
                'acceso_discapacitados' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resources')->insert([
            'name' => "Consultorio 2 - Especialidades",
            'description' => "Consultorio equipado para especialistas",
            'resource_type_id' => 6,
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'especialidad' => 'especialidades',
                'equipamiento' => ['ecografo', 'electrocardiografo', 'oftalmoscopio'],
                'camilla_especial' => true,
                'computadora' => true,
                'privacidad' => true,
                'acceso_discapacitados' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resources')->insert([
            'name' => "Consultorio 3 - Exámenes",
            'description' => "Consultorio para exámenes médicos completos",
            'resource_type_id' => 6,
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'especialidad' => 'examenes',
                'equipamiento' => ['balanza_medica', 'tallimetro', 'espirometro'],
                'camilla_examenes' => true,
                'computadora' => true,
                'privacidad' => true,
                'acceso_discapacitados' => true,
                'area_preparacion' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================== SALA DE ESPERA ====================
        DB::table('resources')->insert([
            'name' => "Sala de Espera Principal",
            'description' => "Sala de espera compartida para pacientes/visitantes",
            'resource_type_id' => 7, // Sala de Espera
            'capacity' => 30,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'climatizada' => true,
                'wifi' => true,
                'revistas' => true,
                'tv' => true,
                'sillon_comodo' => true,
                'mesas' => true,
                'acceso_baños' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Recursos creados: Parking (3 recursos, 100 lugares), Hotel (10 habitaciones), Consultorios (3), Sala de espera (1)');
    }
}