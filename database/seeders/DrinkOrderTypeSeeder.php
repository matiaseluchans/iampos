<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkOrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderTypes = [
            'Venta',
            'Pedido',
            'Cambio',
            'Devolucion'
        ];

        $orderTypesData = [];
        foreach ($orderTypes as $orderType) {
            $orderTypesData[] = [
                'name' => $orderType,
                'active' => true,
                'tenant_id' => 2, // Asumiendo que es para el tenant_id 2 como los anteriores
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('order_types')->insert($orderTypesData);
    }
}
