<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusCodesSeeder extends Seeder
{
    public function run(): void
    {
        // Mapeo de nombre exacto => code estandarizado
        $map = [
            'Pagada'             => 'paid',
            'Cerrada'            => 'paid',
            'Finalizada'         => 'paid',
            'Completa'             => 'completed',
            'Completo'             => 'completed',
            'Pendiente'          => 'pending',
            'Pendiente de pago'  => 'pending',
            'Cancelada'          => 'cancelled',
            'Cancelado'          => 'cancelled',
            'Confirmada'          => 'confirm',
            'Confirmado'          => 'confirm',
            'En Proceso'          => 'process',
            'Procesando'          => 'process',            
            'Pagado'             => 'paid',                                    
            'Pago Parcial'        => 'partial_payment',
            'Reembolso'        => 'refund',

            'No requiere envío'             => 'not_required',            
            'Preparando'         => 'preparing',
            'Listo para recoger'             => 'ready_pickup',
            'Enviado'             => 'shipped',
            'En tránsito'          => 'in_transit',
            'Entregado'  => 'delivered',
            'Entrega fallida'          => 'failed',
            'Devuelto'          => 'returned',            
        ];

        foreach ($map as $name => $code) {
            $updated = DB::table('statuses')
            ->where('name', $name)
            ->whereNull('code')
            ->update(['code' => $code]);

            $this->command->info("Actualizados $updated registros con name = '$name' → code = '$code'");
        }
    }
}
