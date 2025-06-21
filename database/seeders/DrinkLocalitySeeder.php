<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkLocalitySeeder extends Seeder
{
    public function run()
    {
        $localities = [
            'Caseros',
            'Ciudadela',
            'Haedo',
            'Lomas del Mirador',
            'Palomar',
            'Ramos Mejia',
            'Villa Luzuriaga'
        ];

        $localityData = [];
        foreach ($localities as $locality) {
            $localityData[] = [
                'name' => $locality,
                'active' => true,
                'tenant_id' => 2, // Asumiendo que es para el tenant_id 2 como los anteriores
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('localities')->insert($localityData);
    }
}
