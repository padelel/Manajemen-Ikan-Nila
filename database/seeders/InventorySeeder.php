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
            'nama_pakan' => 'Hi-Pro-Vite 781',
            'total_stok_kg' => 500,
            'terakhir_update' => Carbon::now(),
        ]);
    }
}