<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KolamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kolams')->insert([
            ['nama_kolam' => 'Kolam A1', 'dimensi' => '5x10 Meter', 'jumlah_ikan' => 5000, 'berat_rata_gram' => 150, 'deskripsi' => 'Fase Pembesaran'],
            ['nama_kolam' => 'Kolam B1', 'dimensi' => '5x10 Meter', 'jumlah_ikan' => 4800, 'berat_rata_gram' => 120, 'deskripsi' => 'Fase Pendederan'],
        ]);
    }
}