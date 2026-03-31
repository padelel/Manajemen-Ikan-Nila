<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MortalityLogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mortality_logs')->insert([
            'kolam_id' => 1,
            'user_id' => 2,
            'tanggal_kematian' => Carbon::now(),
            'jumlah_mati' => 5,
            'catatan' => 'Terapung di pojok saringan',
        ]);
    }
}