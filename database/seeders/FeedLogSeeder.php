<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedLogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Simpan Header
        $feedLogId = DB::table('feed_logs')->insertGetId([
            'kolam_id' => 1,
            'rule_id' => 1,
            'user_id' => 2,
            'tanggal_pakan' => Carbon::now()->toDateString(),
            'rekomendasi_sistem' => 22.5,
            'pakan_aktual' => 22.5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Simpan Rincian
        DB::table('feed_log_details')->insert([
            'feed_log_id' => $feedLogId, 
            'inventory_id' => 1,         
            'rasio' => 1,                
            'jumlah_kg' => 22.5,         
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}