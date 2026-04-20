<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventories')->insert([
            [
                'nama_pakan' => 'Pakan 20% Protein',
                'total_stok_kg' => 250,
                'terakhir_update' => Carbon::now(),
            ],
            [
                'nama_pakan' => 'Pakan 15% Protein',
                'total_stok_kg' => 150,
                'terakhir_update' => Carbon::now(),
            ],
            [
                'nama_pakan' => 'Ampas Kelapa',
                'total_stok_kg' => 300,
                'terakhir_update' => Carbon::now(),
            ]
        ]);
    }
}