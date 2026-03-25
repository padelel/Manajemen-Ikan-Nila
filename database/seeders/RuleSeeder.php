<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rule;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['kode_rule' => 'R01', 'kondisi_suhu' => '25-32', 'kondisi_ph' => '6.5-8.0', 'kondisi_visual' => 'Jernih', 'rekomendasi_dosis_persen' => 100],
            ['kode_rule' => 'R02', 'kondisi_suhu' => '< 25', 'kondisi_ph' => '6.5-8.0', 'kondisi_visual' => 'Jernih', 'rekomendasi_dosis_persen' => 50],
            ['kode_rule' => 'R03', 'kondisi_suhu' => '> 32', 'kondisi_ph' => '6.5-8.0', 'kondisi_visual' => 'Jernih', 'rekomendasi_dosis_persen' => 50],
            ['kode_rule' => 'R04', 'kondisi_suhu' => '25-32', 'kondisi_ph' => '6.5-8.0', 'kondisi_visual' => 'Keruh', 'rekomendasi_dosis_persen' => 50],
            ['kode_rule' => 'R05', 'kondisi_suhu' => 'Semua', 'kondisi_ph' => '< 6.5', 'kondisi_visual' => 'Berbusa', 'rekomendasi_dosis_persen' => 0],
            ['kode_rule' => 'R06', 'kondisi_suhu' => 'NULL', 'kondisi_ph' => 'NULL', 'kondisi_visual' => 'NULL', 'rekomendasi_dosis_persen' => 80],
        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }
    }
}