<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PetshopOrderItemsTableSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 3; // ID del inquilino para el que se están generando los pedidos
        $this->command->info("Generando pedidos para el inquilino con ID: {$tenantId}");
        $faker = Faker::create();
        $orderIds = DB::table('orders')->where('tenant_id', $tenantId)->pluck('id')->toArray();
        $productIds = DB::table('products')->where('tenant_id', $tenantId)->pluck('id')->toArray();

        $orderItems = [];
        $now = Carbon::now();

        foreach ($orderIds as $orderId) {
            $itemsCount = $faker->numberBetween(1, 8);
            
            for ($i = 0; $i < $itemsCount; $i++) {
                $quantity = $faker->numberBetween(1, 5);
                $unitPrice = $faker->randomFloat(2, 10, 500);
                $unitCostPrice = $unitPrice * $faker->randomFloat(2, 0.4, 0.8);
                $discountAmount = $faker->randomFloat(2, 0, $unitPrice * 0.2);
                $totalPrice = ($unitPrice * $quantity) - $discountAmount;
                $totalProfit = ($unitPrice - $unitCostPrice) * $quantity - $discountAmount;

                $orderItems[] = [
                    'order_id' => $orderId,
                    'product_id' => $faker->randomElement($productIds),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost_price' => $unitCostPrice,
                    'discount_amount' => $discountAmount,
                    'total_price' => $totalPrice,
                    'total_profit' => $totalProfit,
                    'created_by' => 1,
                    'last_modified_by' => 1,
                    'deleted_by' => null,
                    'tenant_id' => $tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ];
            }
        }

        // Insertar en lotes para mejor rendimiento
        foreach (array_chunk($orderItems, 1000) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }
    }
}
