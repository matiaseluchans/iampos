<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DrinkOrdersTableSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 2; // ID del inquilino para el que se están generando los pedidos
        $this->command->info("Generando pedidos para el inquilino con ID: {$tenantId}");
        $faker = Faker::create();
        $statusIds = DB::table('statuses')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $orderTypeIds = DB::table('order_types')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $customerIds = DB::table('customers')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $sellerIds = DB::table('users')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $shipmentStatusIds = DB::table('shipment_statuses')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $paymentStatusIds = DB::table('payment_statuses')->where('tenant_id', $tenantId)->pluck('id')->toArray();

        $orders = [];
        $now = Carbon::now();
        
        // Prefijo y número inicial        
        $startNumber = 1;

        for ($i = 0; $i < 5000; $i++) {
            $orderDate = $faker->dateTimeBetween('-2 years', 'now');
            $deliveryDate = $faker->dateTimeBetween($orderDate, '+30 days');
            $subtotal = $faker->randomFloat(2, 50, 5000);
            $taxAmount = $subtotal * 0.16;
            $discountAmount = $faker->randomFloat(2, 0, $subtotal * 0.3);
            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            $totalCost = $subtotal * $faker->randomFloat(2, 0.4, 0.7);
            $totalProfit = $totalAmount - $totalCost;
            
            // Generar número de pedido con ceros a la izquierda
            $orderNumber = str_pad($startNumber + $i, 10, '0', STR_PAD_LEFT);

            $orders[] = [
                'order_date' => $orderDate,
                'delivery_date' => $faker->boolean(80) ? $deliveryDate : null,
                'order_number' => $orderNumber, // Número correlativo con ceros
                'customer_id' => $faker->randomElement($customerIds),
                'customer_details' => json_encode([
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'phone' => $faker->phoneNumber,
                ]),
                'shipping_address' => json_encode([
                    'street' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'zip_code' => $faker->postcode,
                    'country' => $faker->country,
                ]),
                'status_id' => $faker->randomElement($statusIds),
                'order_type_id' => $faker->randomElement($orderTypeIds),
                'shipping' => $faker->randomFloat(2, 0, 50),
                'quantity_products' => $faker->numberBetween(1, 15),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'notes' => $faker->boolean(30) ? $faker->sentence(10) : null,
                'tenant_id' => $tenantId,
                'created_by' => 1,
                'last_modified_by' => 1,
                'deleted_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
                'seller_id' => $faker->randomElement($sellerIds),
                'seller_name' => $faker->name,
                'shipment_status_id' => $faker->randomElement($shipmentStatusIds),
                'payment_status_id' => $faker->randomElement($paymentStatusIds),
            ];
        }

        // Insertar en lotes para mejor rendimiento
        foreach (array_chunk($orders, 500) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
    }
}