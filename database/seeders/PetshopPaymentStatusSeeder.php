<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopPaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [                        
            //estado de pago
            'Pendiente',
            'Pagado',
            'Pago Parcial',
            'Reembolso',
            'Cancelado',            
        ];        
        foreach ($records as $record) {
            $data[] = [
                'name' => $record,
                'active' => true,
                'tenant_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payment_statuses')->insert($data);
    }
}
