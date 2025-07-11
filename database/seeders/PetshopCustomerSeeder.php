<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PetshopCustomerSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 5; $i++) {
            DB::table('customers')->insert([
                'address'           => "Calle de Prueba $i",
                'telephone'         => "12345678$i",
                'email'             => "cliente$i@tenant3.com",
                'firstname'         => "Nombre$i",
                'lastname'          => "Apellido$i",
                'locality_id'       => 1,
                'companyname'       => "Empresa $i",
                'tenant_id'         => 3,
                'active'            => true,
                'created_by'        => 1,
                'last_modified_by'  => 1,
                'deleted_by'        => 1,
            ]);
        }

        $customers[] = [
                'address' => 'General',
                'email' => 'general@mail.com',
                'firstname' => 'General',
                'lastname' => 'General',
                'tenant_id' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
        ];

        DB::table('customers')->insert($customers);
    }
}
