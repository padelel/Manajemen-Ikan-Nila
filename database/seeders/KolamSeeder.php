<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KolamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kolams')->insert([
            [
                'nama_kolam' => 'Kolam A1', 
                'lokasi' => 'Blok Utara',
                'bentuk_kolam' => 'Bundar',
                'status_kolam' => 'Aktif',
                'panjang_m' => 4, // Jika bundar, panjang = diameter
                'lebar_m' => 4,
                'kedalaman_m' => 1.2,
                'tanggal_tebar' => Carbon::now()->subDays(30)->toDateString(),
                'jumlah_ikan' => 1000, 
                'berat_rata_gram' => 150
            ],
            [
                'nama_kolam' => 'Kolam B1', 
                'lokasi' => 'Blok Selatan',
                'bentuk_kolam' => 'Persegi',
                'status_kolam' => 'Aktif',
                'panjang_m' => 5,
                'lebar_m' => 10,
                'kedalaman_m' => 1.5,
                'tanggal_tebar' => Carbon::now()->subDays(60)->toDateString(),
                'jumlah_ikan' => 1100, 
                'berat_rata_gram' => 210
            ],
        ]);
    }
}