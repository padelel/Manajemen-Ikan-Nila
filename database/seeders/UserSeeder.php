<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Pengelola Utama',
                'email' => 'admin@nila.com',
                'password' => Hash::make('password123'),
                'role' => 'admin', // Opsional jika Anda nanti pakai role
            ],
            [
                'name' => 'Operator Lapangan',
                'email' => 'operator@nila.com',
                'password' => Hash::make('password123'),
                'role' => 'operator',
            ]
        ]);
    }
}