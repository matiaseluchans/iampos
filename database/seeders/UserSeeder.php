<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Matias Eluchans',
            'email' => 'matiaseluchans@gmail.com',
            'password' => Hash::make('1q2w3e4r'),
        ]);

        User::create([
            'name' => 'Luis Quintero',
            'email' => 'luis.quintero1983@gmail.com',
            'password' => Hash::make('1q2w3e4r'),
        ]);
    }
}
