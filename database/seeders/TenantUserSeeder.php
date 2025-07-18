<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        // === Crear un tenant especial para superadmin ===
        $systemTenant = Tenant::firstOrCreate([

            'id' => 1,
            'slug' => 'admin',
            'name' => 'Administrador',
        ]);

        // === Crear rol superadmin (sin tenant_id o con el del sistema) ===
        $superadminRole = Role::firstOrCreate([
            'name' => 'id',
            'name' => 'superadmin',
            'tenant_id' => $systemTenant->id,
        ], [
            'description' => 'Acceso total a todo el sistema',
        ]);

        // === Crear usuario superadmin ===
        $superadmin = User::firstOrCreate([
            'email' => 'matiaseluchans@gmail.com',
        ], [
            'name' => 'Matias Eluchans',
            'password' => Hash::make('1q2w3e4r'),
            'tenant_id' => $systemTenant->id,
            'image' => 'matias eluchans_6858888f33926.png'
        ]);
        $superadmin2 = User::firstOrCreate([
            'email' => 'luis.quintero1983@gmail.com',
        ], [
            'name' => 'Luis Quintero',
            'password' => Hash::make('1q2w3e4r'),
            'tenant_id' => $systemTenant->id,
        ]);

        $superadmin->roles()->syncWithoutDetaching([$superadminRole->id]);
        $superadmin2->roles()->syncWithoutDetaching([$superadminRole->id]);



        // === Crear Tenants y sus administradores/usuarios ===
        $tenants = [
            [
                "id" => 2,
                "slug" => "bebidas",
                "name" => "Bebidas R.L",
                "address" => "Coronel Acha 785 R. Mejia",
                "telephone" => "1165897555",
                "email" => "ventasbebidas@bebidas.com"
            ],
            [
                "id" => 3,
                "slug" => "petshop",
                "name" => "pet shop Droopy",
                "address" => "Av. Eva Peron 98 R. Mejia",
                "telephone" => "1165897555",
                "email" => "ventas@droopy.com"
            ]
        ];

        foreach ($tenants as $tenantData) {
            // Crear o actualizar tenant
            $tenant = Tenant::updateOrCreate(
                ['id' => $tenantData['id']],
                [
                    'slug' => $tenantData['slug'],
                    'name' => $tenantData['name'],
                    'address' => $tenantData['address'],
                    'telephone' => $tenantData['telephone'],
                    'email' => $tenantData['email']
                ]
            );

            // Crear roles para el tenant
            $adminRole = Role::updateOrCreate(
                [
                    'name' => $tenantData['slug'] . '-admin',
                    'tenant_id' => $tenant->id
                ],
                ['description' => 'Administrador del tenant']
            );

            $userRole = Role::updateOrCreate(
                [
                    'name' => $tenantData['slug'] . '-user',
                    'tenant_id' => $tenant->id
                ],
                ['description' => 'Usuario básico del tenant']
            );

            // Crear usuario admin
            $admin = User::updateOrCreate(
                ['email' => $tenantData['slug'] . "_admin@example.com"],
                [
                    'name' => $tenantData['name'] . " Admin",
                    'password' => Hash::make('1q2w3e4r'),
                    'tenant_id' => $tenant->id
                ]
            );
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);

            // Crear usuarios básicos
            foreach (range(1, 2) as $index) {
                $user = User::updateOrCreate(
                    ['email' => $tenantData['slug'] . "_user_" . $index . "@example.com"],
                    [
                        'name' => $tenantData['name'] . " User " . $index,
                        'password' => Hash::make('1q2w3e4r'),
                        'tenant_id' => $tenant->id
                    ]
                );
                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }
        }
    }
}
