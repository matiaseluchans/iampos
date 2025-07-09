<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapeo de nombre exacto => code estandarizado
        $map = [
            'Pagada'             => 'paid',
            'Pagado'             => 'paid',
            'Cerrada'            => 'paid',
            'Finalizada'         => 'paid',
            'Completa'             => 'paid',
            'Completo'             => 'paid',
            'Pendiente'          => 'pending',
            'Pendiente de pago'  => 'pending',
            'Cancelada'          => 'cancelled',
            'Cancelado'          => 'cancelled',
            'Confirmada'          => 'confirm',
            'Confirmado'          => 'confirm',
            'En Proceso'          => 'process',
            'Procesando'          => 'process',
            'Pago Parcial'        => 'partial_payment',
            'Reembolso'        => 'refund',
        ];

        foreach ($map as $name => $code) {
            $updated = DB::table('payment_statuses')
            ->where('name', $name)
            ->whereNull('code')
            ->update(['code' => $code]);

            $this->command->info("Actualizados $updated registros con name = '$name' → code = '$code'");
        }
    }
}
