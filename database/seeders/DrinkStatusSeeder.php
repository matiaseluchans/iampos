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
