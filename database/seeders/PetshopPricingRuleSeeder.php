<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopPricingRuleSeeder extends Seeder
{
    public function run()
    {
        // Descuento para razas grandes (más de 25kg)
        DB::table('pricing_rules')->insert([
            'service_type_id' => 2, // Corte de Pelo Completo
            'name' => 'Recargo Razas Grandes',
            'description' => 'Recargo adicional para razas de más de 25kg',
            'conditions' => json_encode([
                [
                    'type' => 'dog_weight',
                    'operator' => '>',
                    'value' => 25
                ]
            ]),
            'modification_type' => 'percentage',
            'modification_value' => 20, // 20% de recargo
            'priority' => 1,
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        

        // Descuento para múltiples perros
        DB::table('pricing_rules')->insert([
            'service_type_id' => 1, // Baño y Secado Básico
            'name' => 'Descuento Múltiples Mascotas',
            'description' => '10% de descuento al reservar para 2 o más perros',
            'conditions' => json_encode([
                [
                    'type' => 'multiple_pets',
                    'operator' => '>=',
                    'value' => 2
                ]
            ]),
            'modification_type' => 'percentage',
            'modification_value' => -10, // -10% de descuento
            'priority' => 2,
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);        

        // Recargo fin de semana
        DB::table('pricing_rules')->insert([
            'service_type_id' => null, // Aplica a todos los servicios
            'name' => 'Recargo Fin de Semana',
            'description' => '15% de recargo los sábados y domingos',
            'conditions' => json_encode([
                [
                    'type' => 'day_of_week',
                    'value' => [6, 0] // Sábado y Domingo
                ]
            ]),
            'modification_type' => 'percentage',
            'modification_value' => 15,
            'priority' => 3,
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        

        // Descuento horario matutino (entre 9:00 y 12:00)
        DB::table('pricing_rules')->insert([
            'service_type_id' => null, // Aplica a todos los servicios
            'name' => 'Descuento Horario Matutino',
            'description' => '10% de descuento en horario de 9:00 a 12:00',
            'conditions' => json_encode([
                [
                    'type' => 'time_range',
                    'start' => '09:00',
                    'end' => '12:00'
                ]
            ]),
            'modification_type' => 'percentage',
            'modification_value' => -10,
            'priority' => 4,
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);        

        // Precio fijo para corte de uñas los miércoles
        DB::table('pricing_rules')->insert([
            'service_type_id' => 5, // Corte de Uñas
            'name' => 'Miércoles de Uñas',
            'description' => 'Precio especial de €8 los miércoles para corte de uñas',
            'conditions' => json_encode([
                [
                    'type' => 'day_of_week',
                    'value' => [3] // Miércoles
                ]
            ]),
            'modification_type' => 'fixed_rate',
            'modification_value' => 8.00,
            'priority' => 1,
            'is_active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);        

        $this->command->info('Reglas de precios para peluquería canina creadas exitosamente');
    }
}