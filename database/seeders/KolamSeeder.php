<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kolam;

class KolamSeeder extends Seeder
{
    public function run(): void
    {
        Kolam::create([
            'nama_kolam' => 'Kolam Utama Duren Sawit',
            'dimensi' => '5x10 Meter',
            'jumlah_ikan' => 5000, // Asumsi tebar 5.000 ekor
            'deskripsi' => 'Kolam pembesaran tahap akhir.'
        ]);
    }
}