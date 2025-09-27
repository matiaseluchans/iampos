<?php

namespace Database\Seeders;

//use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopResourceSeeder extends Seeder
{
    public function run()
    {
        // Estaciones de peluquería
        DB::table('resources')->insert([
            'name' => 'Estación 1 - Premium',
            'description' => 'Estación de peluquería premium con bañera hidromasaje y equipos profesionales',
            'resource_type_id' => 1,
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'bañera_hidromasaje' => true,
                'mesa_hidraulica' => true,
                'secador_profesional' => true,
                'iluminacion_led' => true,
                'acceso_agua_caliente' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        //Resource::create();

        DB::table('resources')->insert([
            'name' => 'Estación 2 - Estándar',
            'description' => 'Estación de peluquería estándar con todos los equipos necesarios',
            'resource_type_id' => 1,
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'bañera_estandar' => true,
                'mesa_ajustable' => true,
                'secador_profesional' => true,
                'iluminacion_natural' => true,
                'acceso_agua_caliente' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resources')->insert([
            'name' => 'Estación 3 - Para Razas Grandes',
            'description' => 'Estación especial para razas grandes con espacio amplio y equipos reforzados',
            'resource_type_id' => 1,
            'capacity' => 1,
            'current_usage' => 0,
            'is_shared' => false,
            'features' => json_encode([
                'bañera_amplia' => true,
                'mesa_reforzada' => true,
                'secador_industrial' => true,
                'espacio_amplio' => true,
                'acceso_agua_caliente' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sala de espera
        DB::table('resources')->insert([
            'name' => 'Sala de Espera Principal',
            'description' => 'Sala de espera climatizada con jaulas individuales y área de socialización',
            'resource_type_id' => 2,
            'capacity' => 10,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'jaulas_individuales' => 10,
                'climatizacion' => true,
                'camaras_seguridad' => true,
                'area_socializacion' => true,
                'piso_antideslizante' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Área de secado
        DB::table('resources')->insert([
            'name' => 'Área de Secado 1',
            'description' => 'Área de secado con cabinas individuales y secadores profesionales',
            'resource_type_id' => 3,
            'capacity' => 3,
            'current_usage' => 0,
            'is_shared' => true,
            'features' => json_encode([
                'cabinas_individuales' => 3,
                'secadores_profesionales' => 3,
                'ventilacion' => true,
                'temperatura_controlada' => true
            ]),
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Recursos de peluquería canina creados exitosamente');
    }
}