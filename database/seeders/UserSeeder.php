<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Supervisor
        User::create([
            'name' => 'Bapak Supervisor',
            'email' => 'supervisor@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        // 2. Akun Operator Lapangan
        User::create([
            'name' => 'Mas Operator',
            'email' => 'operator@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'operator',
            'email_verified_at' => now(),
        ]);
    }
}