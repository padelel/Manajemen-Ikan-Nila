<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyParameterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('daily_parameters')->insert([
            'kolam_id' => 1,
            'user_id' => 2,
            'tanggal_cek' => Carbon::now()->toDateString(),
            'suhu' => 28.5,
            'ph' => 7.2,
            'kondisi_visual' => 'Jernih',
            'berat_sample' => 155.5, // Kolom baru untuk perhitungan FR
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}