<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedLogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Simpan Header ke feed_logs (Tanpa inventory_id)
        // Kita menggunakan insertGetId untuk mengambil ID yang baru saja dibuat
        $feedLogId = DB::table('feed_logs')->insertGetId([
            'kolam_id' => 1,
            'rule_id' => 1,
            'user_id' => 2, // 2 = Operator Lapangan
            'tanggal_pakan' => Carbon::now(),
            'rekomendasi_sistem' => 22.5,
            'pakan_aktual' => 22.5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Simpan Rincian Pakan ke feed_log_details
        DB::table('feed_log_details')->insert([
            'feed_log_id' => $feedLogId, // Relasi ke tabel induk
            'inventory_id' => 1,         // Jenis pakan yang dipakai
            'rasio' => 1,                // Rasio
            'jumlah_kg' => 22.5,         // Alokasi per pakan
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}