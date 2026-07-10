<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Supervisor
        User::updateOrCreate(
            ['email' => 'supervisor@nila.com'],
            [
                'name' => 'Supervisor Kolam',
                'password' => Hash::make('password123'),
                'role' => 'supervisor',
            ]
        );

        // Akun Operator
        User::updateOrCreate(
            ['email' => 'operator@nila.com'],
            [
                'name' => 'Operator Lapangan',
                'password' => Hash::make('password123'),
                'role' => 'operator',
            ]
        );
    }
}
