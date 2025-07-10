<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopStatusSeeder extends Seeder
{
    public function run()
    {
        /*
        DB::table('statuses')->insert([
            'id' => 6,
            'name' => 'Pendiente',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('statuses')->insert([
            'id' => 7,
            'name' => 'Confirmado',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('statuses')->insert([
            'id' => 8,
            'name' => 'Procesando',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('statuses')->insert([
            'id' => 9,
            'name' => 'Completo',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('statuses')->insert([
            'id' => 10,
            'name' => 'Cancelado',
            'active' => true,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);*/


        $records = [
            'Pendiente',
            'Confirmado',
            'Procesando',
            'Completo',
            'Cancelado',
            
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
                'tenant_id' => 3, // Asumiendo que es para el tenant_id 2 como los anteriores
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('statuses')->insert($data);
    }
}
