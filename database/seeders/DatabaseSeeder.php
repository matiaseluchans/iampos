<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //UserSeeder::class,
            TenantUserSeeder::class,
            DrinkBrandSeeder::class,
            DrinkCategorySeeder::class,
            DrinkProductSeeder::class,
            DrinkCustomerSeeder::class,
            DrinkLocalitySeeder::class,
            DrinkWarehouseSeeder::class,

            PetshopBrandSeeder::class,
            PetshopCategorySeeder::class,
            PetshopProductSeeder::class,
            PetshopWarehouseSeeder::class,
        ]);
    }
}
