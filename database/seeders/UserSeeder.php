<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Pengelola (Admin)
        User::create([
            'name' => 'Pengelola Kolam',
            'email' => 'admin@nila.com',
            'password' => Hash::make('password123'),
        ]);

        // Akun Operator Harian
        User::create([
            'name' => 'Operator Duren Sawit',
            'email' => 'operator@nila.com',
            'password' => Hash::make('password123'),
        ]);
    }
}