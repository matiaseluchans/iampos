<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrinkSeeder extends Seeder
{
    public function run()
    {
        $priceLists = [
            [
                'id' => 1,
                'name' => 'mayorista',
                'description' => null,
                'is_default' => 1,
                'active' => 1,
                'tenant_id' => 2,
                'created_by' => null,
                'last_modified_by' => null,
                'deleted_by' => null,
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null
            ],
            [
                'id' => 2,
                'name' => 'alberto',
                'description' => null,
                'is_default' => 0,
                'active' => 1,
                'tenant_id' => 2,
                'created_by' => null,
                'last_modified_by' => '4',
                'deleted_by' => null,
                'created_at' => null,
                'updated_at' => Carbon::create(2025, 8, 27, 15, 16, 14),
                'deleted_at' => null
            ],

        ];

        foreach ($priceLists as $priceList) {
            DB::table('price_lists')->updateOrInsert(
                ['id' => $priceList['id']],
                $priceList
            );
        }
    }
}
