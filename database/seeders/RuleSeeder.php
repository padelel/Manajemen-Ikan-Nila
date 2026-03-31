<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rules')->insert([
            ['kode_rule' => 'R01', 'kondisi_suhu' => '25-32', 'kondisi_ph' => '6.5-8.5', 'kondisi_visual' => 'Jernih', 'rekomendasi_dosis_persen' => 100],
            ['kode_rule' => 'R02', 'kondisi_suhu' => '<25 atau >32', 'kondisi_ph' => '6.5-8.5', 'kondisi_visual' => 'Keruh', 'rekomendasi_dosis_persen' => 50],
            ['kode_rule' => 'R03', 'kondisi_suhu' => 'Bebas', 'kondisi_ph' => '<6.5 atau >8.5', 'kondisi_visual' => 'Berbusa', 'rekomendasi_dosis_persen' => 0],
        ]);
    }
}