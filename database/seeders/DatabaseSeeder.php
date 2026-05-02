<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Rule;
use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\DailyParameter;
use App\Models\FeedLog;
use App\Models\MortalityLog;
use App\Models\HarvestLog;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Kosongkan database dengan aman sebelum diisi
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Rule::truncate();
        Inventory::truncate();
        InventoryLog::truncate();
        Kolam::truncate();
        SiklusBudidaya::truncate();
        TebarLog::truncate();
        DailyParameter::truncate();
        FeedLog::truncate();
        DB::table('feed_log_details')->truncate();
        MortalityLog::truncate();
        HarvestLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Buat Akun Pengguna
        $admin = User::create([
            'name' => 'Pengelola Utama',
            'email' => 'admin@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'admin', 
        ]);

        $operator = User::create([
            'name' => 'Operator Lapangan',
            'email' => 'operator@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'operator',
        ]);

        // 3. Buat Data Master Rule (Sistem Pakar)
        $rules = [
            [
                'kode_rule' => 'R01', 
                'kondisi_suhu' => '25-32', 
                'kondisi_ph' => '6.5-8.5', 
                'kondisi_visual' => 'Jernih', 
                'rekomendasi_dosis_persen' => 100
            ],
            [
                'kode_rule' => 'R02', 
                'kondisi_suhu' => '<25 atau >32', 
                'kondisi_ph' => 'Normal', 
                'kondisi_visual' => 'Keruh', 
                'rekomendasi_dosis_persen' => 50
            ],
            [
                'kode_rule' => 'R03', 
                'kondisi_suhu' => 'Bebas', 
                'kondisi_ph' => '<6.5 atau >8.5', 
                'kondisi_visual' => 'Berbusa', 
                'rekomendasi_dosis_persen' => 0
            ],
        ];
        
        foreach ($rules as $r) { 
            Rule::create($r); 
        }
        $ruleNormal = Rule::where('kode_rule', 'R01')->first();

        // 4. Buat Data Gudang Pakan & Log Awal
        $pakan1 = Inventory::create(['nama_pakan' => 'Hi-Pro-Vite 781-1 (Starter)', 'total_stok_kg' => 500, 'terakhir_update' => now()]);
        $pakan2 = Inventory::create(['nama_pakan' => 'Hi-Pro-Vite 781-2 (Grower)', 'total_stok_kg' => 1200, 'terakhir_update' => now()]);
        $pakan3 = Inventory::create(['nama_pakan' => 'Hi-Pro-Vite 781-3 (Finisher)', 'total_stok_kg' => 800, 'terakhir_update' => now()]);

        // Catat masuknya stok ke gudang
        InventoryLog::insert([
            ['inventory_id' => $pakan1->id, 'user_id' => $admin->id, 'tipe' => 'Masuk', 'jumlah' => 500, 'keterangan' => 'Pembelian Awal Bulan', 'created_at' => now(), 'updated_at' => now()],
            ['inventory_id' => $pakan2->id, 'user_id' => $admin->id, 'tipe' => 'Masuk', 'jumlah' => 1200, 'keterangan' => 'Pembelian Awal Bulan', 'created_at' => now(), 'updated_at' => now()],
            ['inventory_id' => $pakan3->id, 'user_id' => $admin->id, 'tipe' => 'Masuk', 'jumlah' => 800, 'keterangan' => 'Pembelian Awal Bulan', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================================
        // SKENARIO 1: KOLAM A (SIKLUS BARU DIMULAI 6 HARI LALU - AKTIF)
        // =========================================================================
        $kolamA = Kolam::create([
            'nama_kolam' => 'Kolam Terpal A (Starter)', 'lokasi' => 'Blok Utara', 'bentuk_kolam' => 'Bundar',
            'status_kolam' => 'Aktif', 'panjang_m' => 4, 'lebar_m' => 4, 'kedalaman_m' => 1.2,
            'jumlah_ikan' => 990, 'berat_rata_gram' => 45.0 
        ]);

        $tanggalTebarA = Carbon::today()->subDays(6); // Dibuat 6 hari lalu agar grafiknya naik di dashboard
        
        SiklusBudidaya::create(['kolam_id' => $kolamA->id, 'tanggal_mulai' => $tanggalTebarA, 'status_aktif' => 'Aktif', 'jumlah_tebar_awal' => 1000]);
        TebarLog::create(['kolam_id' => $kolamA->id, 'user_id' => $admin->id, 'tanggal_tebar' => $tanggalTebarA, 'jumlah_ikan' => 1000, 'berat_rata_gram' => 15, 'sumber_benih' => 'Balai Benih Lokal']);
        MortalityLog::create(['kolam_id' => $kolamA->id, 'user_id' => $operator->id, 'tanggal_kematian' => Carbon::today()->subDays(3), 'jumlah_mati' => 10, 'catatan' => 'Mati adaptasi awal']);

        // Generate grafik harian Kolam A (Hanya 6 hari terakhir)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $berat = 15 + ((6 - $i) * 5); // Pertumbuhan 5 gram/hari
            
            DailyParameter::create([
                'kolam_id' => $kolamA->id, 'user_id' => $operator->id, 'tanggal_cek' => $date,
                'suhu' => rand(27, 29), 'ph' => rand(70, 75) / 10, 'kondisi_visual' => 'Jernih', 'berat_sample' => $berat
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamA->id, 'rule_id' => $ruleNormal->id, 'user_id' => $operator->id,
                'tanggal_pakan' => $date, 'frekuensi' => 3, 'rekomendasi_sistem' => 2.5, 'pakan_aktual' => 2.5
            ]);
            $feed->details()->create(['inventory_id' => $pakan1->id, 'rasio' => 1, 'jumlah_kg' => 2.5]);
        }

        // =========================================================================
        // SKENARIO 2: KOLAM B (SUDAH LAMA, PERNAH PANEN PARSIAL 4 HARI LALU)
        // =========================================================================
        $kolamB = Kolam::create([
            'nama_kolam' => 'Kolam Beton B (Grower)', 'lokasi' => 'Blok Selatan', 'bentuk_kolam' => 'Persegi',
            'status_kolam' => 'Aktif', 'panjang_m' => 10, 'lebar_m' => 5, 'kedalaman_m' => 1.5,
            'jumlah_ikan' => 1485, 'berat_rata_gram' => 220 
        ]);

        $tanggalTebarB = Carbon::today()->subDays(45);
        
        SiklusBudidaya::create(['kolam_id' => $kolamB->id, 'tanggal_mulai' => $tanggalTebarB, 'status_aktif' => 'Aktif', 'jumlah_tebar_awal' => 2000]);
        TebarLog::create(['kolam_id' => $kolamB->id, 'user_id' => $admin->id, 'tanggal_tebar' => $tanggalTebarB, 'jumlah_ikan' => 2000, 'berat_rata_gram' => 50, 'sumber_benih' => 'Pendederan A']);
        
        MortalityLog::create(['kolam_id' => $kolamB->id, 'user_id' => $operator->id, 'tanggal_kematian' => Carbon::today()->subDays(20), 'jumlah_mati' => 15, 'catatan' => 'Sakit jamur']);
        // Panen parsial 500 ekor 4 hari yang lalu (Agar kelihatan turun sedikit di chart)
        HarvestLog::create(['kolam_id' => $kolamB->id, 'user_id' => $admin->id, 'jenis_panen' => 'Parsial', 'tanggal_panen' => Carbon::today()->subDays(4), 'jumlah_ikan' => 500, 'berat_total_kg' => 100, 'catatan' => 'Panen sortir']);

        // Generate grafik 7 hari terakhir untuk Kolam B
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $berat = 190 + ((6 - $i) * 5);
            
            DailyParameter::create([
                'kolam_id' => $kolamB->id, 'user_id' => $operator->id, 'tanggal_cek' => $date,
                'suhu' => rand(26, 28), 'ph' => 7.2, 'kondisi_visual' => 'Jernih', 'berat_sample' => $berat
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamB->id, 'rule_id' => $ruleNormal->id, 'user_id' => $operator->id,
                'tanggal_pakan' => $date, 'frekuensi' => 2, 'rekomendasi_sistem' => 7.0, 'pakan_aktual' => 7.0
            ]);
            // Simulasi multi-pakan
            $feed->details()->create(['inventory_id' => $pakan2->id, 'rasio' => 2, 'jumlah_kg' => 4.6]);
            $feed->details()->create(['inventory_id' => $pakan3->id, 'rasio' => 1, 'jumlah_kg' => 2.4]);
        }

        // =========================================================================
        // SKENARIO 3: KOLAM C (KOSONG, BARU PANEN FULL 2 HARI LALU)
        // =========================================================================
        $kolamC = Kolam::create([
            'nama_kolam' => 'Kolam Tanah C (Finisher)', 'lokasi' => 'Blok Timur', 'bentuk_kolam' => 'Persegi',
            'status_kolam' => 'Aktif', 'panjang_m' => 20, 'lebar_m' => 10, 'kedalaman_m' => 1.8,
            'jumlah_ikan' => 0, 'berat_rata_gram' => 0 // Kosong karena sudah panen full
        ]);

        $tanggalTebarC = Carbon::today()->subDays(120);
        $tanggalPanenC = Carbon::today()->subDays(2); // Panen 2 hari lalu agar grafik terjun bebas ke 0
        
        SiklusBudidaya::create(['kolam_id' => $kolamC->id, 'tanggal_mulai' => $tanggalTebarC, 'tanggal_selesai' => $tanggalPanenC, 'status_aktif' => 'Selesai', 'jumlah_tebar_awal' => 3000]);

        TebarLog::create(['kolam_id' => $kolamC->id, 'user_id' => $admin->id, 'tanggal_tebar' => $tanggalTebarC, 'jumlah_ikan' => 3000, 'berat_rata_gram' => 100, 'sumber_benih' => 'Pendederan B']);
        MortalityLog::create(['kolam_id' => $kolamC->id, 'user_id' => $operator->id, 'tanggal_kematian' => Carbon::today()->subDays(60), 'jumlah_mati' => 150, 'catatan' => 'Cuaca buruk']);
        HarvestLog::create(['kolam_id' => $kolamC->id, 'user_id' => $admin->id, 'jenis_panen' => 'Full', 'tanggal_panen' => $tanggalPanenC, 'jumlah_ikan' => 2850, 'berat_total_kg' => 855, 'catatan' => 'Panen raya akhir siklus']);
        
        // Generate aktivitas hanya sampai hari panen (7 hingga 3 hari yang lalu)
        for ($i = 6; $i >= 3; $i--) {
            $date = Carbon::today()->subDays($i);
            
            DailyParameter::create([
                'kolam_id' => $kolamC->id, 'user_id' => $operator->id, 'tanggal_cek' => $date,
                // UBAH 'Sedikit Keruh' MENJADI 'Keruh' DI SINI:
                'suhu' => 28, 'ph' => 7.0, 'kondisi_visual' => 'Keruh', 'berat_sample' => 290 + ((6 - $i) * 2)
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamC->id, 'rule_id' => $ruleNormal->id, 'user_id' => $operator->id,
                'tanggal_pakan' => $date, 'frekuensi' => 2, 'rekomendasi_sistem' => 12.0, 'pakan_aktual' => 12.0
            ]);
            $feed->details()->create(['inventory_id' => $pakan3->id, 'rasio' => 1, 'jumlah_kg' => 12.0]);
        }
    }
}