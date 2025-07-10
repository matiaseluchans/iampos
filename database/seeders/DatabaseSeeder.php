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
            DrinkStockSeeder::class,
            DrinkStatusSeeder::class,            
            DrinkOrderTypeSeeder::class,            
            DrinkPaymentMethodSeeder::class,

            PetshopBrandSeeder::class,
            PetshopCategorySeeder::class,
            PetshopProductSeeder::class,
            PetshopWarehouseSeeder::class,
            PetshopLocalitySeeder::class,
            PetshopCustomerSeeder::class,
            PetshopStockSeeder::class,
            PetshopStatusSeeder::class,
            PetshopPaymentMethodSeeder::class,

            StatusCodesSeeder::class,
        
        ]);
    }
}
