<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        Inventory::create([
            'nama_pakan' => 'Pelet Nila (Hi-Pro-Vite)',
            'total_stok_kg' => 150, // Simulasi stok 150 Kg
            'terakhir_update' => Carbon::now()->toDateString(),
        ]);
    }
}