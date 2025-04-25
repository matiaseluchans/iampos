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

            'slug' => 'admin_system',
            'nombre' => 'Admin System',
        ]);

        // === Crear rol superadmin (sin tenant_id o con el del sistema) ===
        $superadminRole = Role::firstOrCreate([
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
                "slug" => "bedidas",
                "nombre" => "Bebidas"
            ],
            [
                "slug" => "petshop",
                "nombre" => "pet shop"
            ]

        ];
        foreach ($tenants as $k => $v) {

            $tenant = Tenant::firstOrCreate([
                'slug' => $v["slug"],
            ], [
                'nombre' => $v["nombre"],
            ]);

            // Rol admin tenant
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'tenant_id' => $tenant->id,
            ], [
                'description' => 'Administrador del tenant',
            ]);

            // Rol usuario básico
            $userRole = Role::firstOrCreate([
                'name' => 'user',
                'tenant_id' => $tenant->id,
            ], [
                'description' => 'Usuario básico del tenant',
            ]);

            // Crear admin del tenant
            $admin = User::firstOrCreate([
                'email' => $v["slug"] . "_admin@example.com",
            ], [
                'name' => $v["nombre"] . " Admin ",
                'password' => Hash::make('1q2w3e4r'),
                'tenant_id' => $tenant->id,
            ]);

            $admin->roles()->syncWithoutDetaching([$adminRole->id]);

            // Crear usuarios básicos
            foreach (range(1, 2) as $u) {
                $user = User::firstOrCreate([
                    'email' =>  $v["slug"] . "_user_" . $u . "@example.com",
                ], [
                    'name' => $v["nombre"] . " User " . $u,
                    'password' => Hash::make('1q2w3e4r'),
                    'tenant_id' => $tenant->id,
                ]);

                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }
        }
    }
}
