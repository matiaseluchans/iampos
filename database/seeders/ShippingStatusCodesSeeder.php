<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingStatusCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapeo de nombre exacto => code estandarizado
        $map = [
            'No requiere envío'             => 'not_required',
            'Pendiente'            => 'pending',
            'Preparando'         => 'preparing',
            'Listo para recoger'             => 'ready_pickup',
            'Enviado'             => 'shipped',
            'En tránsito'          => 'in_transit',
            'Entregado'  => 'delivered',
            'Entrega fallida'          => 'failed',
            'Devuelto'          => 'returned',
            'Cancelado'          => 'cancelled',            
        ];

        foreach ($map as $name => $code) {
            $updated = DB::table('shipping_statuses')
            ->where('name', $name)
            ->whereNull('code')
            ->update(['code' => $code]);

            $this->command->info("Actualizados $updated registros con name = '$name' → code = '$code'");
        }
    }
}
