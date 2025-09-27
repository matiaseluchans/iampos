<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetshopReservationSeeder extends Seeder
{
    public function run()
    {
        // Reserva 1: Baño básico para Labrador
        DB::table('reservations')->insert([
            'user_id' => 1, // Ana López
            'service_type_id' => 1, // Baño y Secado Básico
            'resource_id' => 1, // Estación 1 - Premium
            'start_time' => Carbon::now()->addDays(1)->setTime(10, 0),
            'end_time' => Carbon::now()->addDays(1)->setTime(11, 00),
            'time_units' => 1,
            'required_capacity' => 1,
            'status' => 'confirmed',
            'participants_count' => 1,
            'special_requirements' => 'Perro Labrador de 30kg, usar shampoo hipoalergénico',
            'total_price' => 25.00,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),            
        ]);
        
        DB::table('reservations')->insert([
            'user_id' => 1, // Elena Sánchez
            'service_type_id' => 2, // Corte de Pelo Completo
            'resource_id' => 2, // Estación 2 - Estándar
            'start_time' => Carbon::now()->addDays(2)->setTime(11, 30),
            'end_time' => Carbon::now()->addDays(2)->setTime(12, 30),
            'time_units' => 1,
            'required_capacity' => 1,
            'status' => 'confirmed',
            'participants_count' => 1,
            'special_requirements' => 'Caniche toy, corte estilo "cachorro"',
            'total_price' => 45.00,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //$reservation1->generateQuotation();

        // Reserva 2: Corte completo para Caniche        

        //$reservation2->generateQuotation();

        // Reserva 3: Spa premium para Pastor Alemán (con recargo por raza grande)
        DB::table('reservations')->insert([
            'user_id' => 2, // Javier García
            'service_type_id' => 4, // Spa Canino Premium
            'resource_id' => 3, // Estación 3 - Para Razas Grandes
            'start_time' => Carbon::now()->addDays(3)->setTime(15, 0),
            'end_time' => Carbon::now()->addDays(3)->setTime(15, 0)->addMinutes(90),
            'time_units' => 1,
            'required_capacity' => 1,
            'status' => 'pending',
            'participants_count' => 1,
            'special_requirements' => 'Pastor Alemán de 35kg, tiene miedo a los secadores',
            'total_price' => 78.00, // 65 + 20% = 78
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);        

        //$quotation3 = $reservation3->generateQuotation();
        /*$quotation3->update([
            'base_price' => 65.00,
            'tax_amount' => 16.38,
            'total_price' => 94.38 // 78 + 21% IVA
        ]);*/

        // Reserva 4: Múltiples servicios para dos perros
        DB::table('reservations')->insert([
            'user_id' => 2, // Ana López
            'service_type_id' => 1, // Baño y Secado Básico
            'resource_id' => 1, // Estación 1 - Premium
            'start_time' => Carbon::now()->addDays(5)->setTime(9, 0),
            'end_time' => Carbon::now()->addDays(5)->setTime(9, 0)->addMinutes(90),
            'time_units' => 2, // Dos horas (dos perros)
            'required_capacity' => 1,
            'status' => 'confirmed',
            'participants_count' => 2,
            'special_requirements' => 'Dos Golden Retrievers, hermanos de 2 años',
            'total_price' => 45.00, // 25 * 2 = 50 - 10% descuento = 45
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);  
        //$reservation4 = Reservation::create();

        /*$quotation4 = $reservation4->generateQuotation();
        $quotation4->update([
            'base_price' => 50.00,
            'discount_amount' => 5.00,
            'tax_amount' => 9.45,
            'total_price' => 54.45
        ]);*/

        // Reserva 5: Corte de uñas en miércoles con precio especial
        DB::table('reservations')->insert([
            'user_id' => 2, // Elena Sánchez
            'service_type_id' => 5, // Corte de Uñas
            'resource_id' => 2, // Estación 2 - Estándar
            'start_time' => Carbon::now()->next(Carbon::WEDNESDAY)->setTime(16, 0),
            'end_time' => Carbon::now()->next(Carbon::WEDNESDAY)->setTime(16, 0)->addMinutes(90),
            'time_units' => 1,
            'required_capacity' => 1,
            'status' => 'confirmed',
            'participants_count' => 1,
            'special_requirements' => 'Caniche toy, muy nervioso con el corte de uñas',
            'total_price' => 8.00,
            'tenant_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);  
        /*$reservation5 = Reservation::create();

        $reservation5->generateQuotation();*/

        $this->command->info('Reservas de ejemplo para peluquería canina creadas exitosamente');
    }
}