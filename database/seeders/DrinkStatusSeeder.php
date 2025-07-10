<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        $records = [
            'Pendiente',
            'Confirmada',
            'En Proceso',
            'Completa',
            'Cancelada',
            
            //estado de pago
            //'Pendiente',
            'Pagado',
            'Pago Parcial',
            'Reembolso',
            //'Cancelado',

            //estados de envio
            'No requiere envío',
            //'Pendiente',
            //'Preparando',
            'Listo para recoger',
            'Enviado',
            'En tránsito',
            'Entregado',
            'Entrega fallida',
            'Devuelto',
            //'Cancelado',
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

        DB::table('statuses')->insert($data);
    }
}
