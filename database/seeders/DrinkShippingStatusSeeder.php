<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkShippingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        $records = [
                'No requiere envío',
                'Pendiente',
                'Preparando',
                'Listo para recoger',
                'Enviado',
                'En tránsito',
                'Entregado',
                'Entrega fallida',
                'Devuelto',
                'Cancelado',
        ];        
        foreach ($records as $record) {
            $data[] = [
                'name' => $record,
                'active' => true,
                'tenant_id' => 2, // Asumiendo que es para el tenant_id 2 como los anteriores
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('shipping_statuses')->insert($data);
    }
}
