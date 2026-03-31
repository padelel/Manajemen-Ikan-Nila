<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedLogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('feed_logs')->insert([
            'kolam_id' => 1,
            'rule_id' => 1,
            'inventory_id' => 1,
            'user_id' => 2,
            'tanggal_pakan' => Carbon::now(),
            'rekomendasi_sistem' => 22.5, // Misal hasil hitung 3% biomassa
            'pakan_aktual' => 22.5,
        ]);
    }
}