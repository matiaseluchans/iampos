<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {                
        $records = [
            'Efectivo',
            'Tarjeta',
            'Transferencia',
            'Billetera digital',
            'Cuenta corriente',            
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

        DB::table('payment_methods')->insert($data);
    }
}
