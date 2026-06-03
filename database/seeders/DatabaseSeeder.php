<?php

namespace Database\Seeders;

use App\Models\DailyParameter;
use App\Models\FeedLog;
use App\Models\HarvestLog;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\Rule;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // RESET DATABASE
        // =========================================================================
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

        // =========================================================================
        // PENGGUNA
        // =========================================================================
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

        // =========================================================================
        // RULE SISTEM PAKAR
        // =========================================================================
        $rules = [
            [
                'kode_rule' => 'R01',
                'kondisi_suhu' => '25-32',
                'kondisi_ph' => '6.5-8.5',
                'kondisi_visual' => 'Jernih',
                'rekomendasi_dosis_persen' => 100,
            ],
            [
                'kode_rule' => 'R02',
                'kondisi_suhu' => '<25 atau >32',
                'kondisi_ph' => '6.5-8.5',
                'kondisi_visual' => 'Keruh',
                'rekomendasi_dosis_persen' => 50,
            ],
            [
                'kode_rule' => 'R03',
                'kondisi_suhu' => 'Bebas',
                'kondisi_ph' => '<6.5 atau >8.5',
                'kondisi_visual' => 'Berbusa',
                'rekomendasi_dosis_persen' => 0,
            ],
        ];
        foreach ($rules as $r) {
            Rule::create($r);
        }
        $ruleNormal = Rule::where('kode_rule', 'R01')->first();

        // =========================================================================
        // GUDANG PAKAN
        // Stok disesuaikan dengan estimasi pemakaian selama siklus masing-masing kolam.
        // =========================================================================
        $pakan1 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-1 (Starter)',
            'total_stok_kg' => 450,
            'terakhir_update' => now(),
        ]);
        $pakan2 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-2 (Grower)',
            'total_stok_kg' => 980,
            'terakhir_update' => now(),
        ]);
        $pakan3 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-3 (Finisher)',
            'total_stok_kg' => 620,
            'terakhir_update' => now(),
        ]);

        InventoryLog::insert([
            [
                'inventory_id' => $pakan1->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => 500,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'inventory_id' => $pakan2->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => 1200,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'inventory_id' => $pakan3->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => 800,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =========================================================================
        // KOLAM A — FASE STARTER → GROWER AWAL (85–190 gram)
        //
        // Skenario: siklus ke-40 hari. Benih ditebar 40 hari lalu dengan berat
        // awal 15 gram/ekor. Saat ini berat rata-rata ±140 gram, masih dalam
        // fase transisi Starter ke Grower. Feed Rate (FR) 5% dari biomassa,
        // pakan dicampur 40% Starter + 60% Grower.
        //
        // Populasi: 1.200 tebar − 50 mati = 1.150 ekor aktif
        // =========================================================================
        $kolamA = Kolam::create([
            'nama_kolam' => 'Kolam Terpal A',
            'lokasi' => 'Blok Utara',
            'bentuk_kolam' => 'Bundar',
            'status_kolam' => 'Aktif',
            'panjang_m' => 4, // diameter untuk kolam bundar
            'lebar_m' => 4,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 1150,
            'berat_rata_gram' => 140.0,
        ]);

        $tanggalTebarA = Carbon::today()->subDays(40);
        SiklusBudidaya::create([
            'kolam_id' => $kolamA->id,
            'tanggal_mulai' => $tanggalTebarA,
            'status_aktif' => 'Aktif',
            'jumlah_tebar_awal' => 1200,
        ]);
        TebarLog::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $admin->id,
            'tanggal_tebar' => $tanggalTebarA,
            'jumlah_ikan' => 1200,
            'berat_rata_gram' => 15,
            'sumber_benih' => 'Balai Benih Ikan Lokal',
        ]);

        // Kematian: 30 (adaptasi awal) + 20 (alami) = 50 → sisa 1.150 ekor
        MortalityLog::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(35),
            'jumlah_mati' => 30,
            'catatan' => 'Kematian masa adaptasi awal, kondisi air belum stabil sepenuhnya',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(20),
            'jumlah_mati' => 20,
            'catatan' => 'Kematian alami, angka masih dalam batas toleransi normal',
        ]);

        // Data harian 7 hari terakhir
        // Pertumbuhan: +3 gram/hari → 122 → 125 → 128 → 131 → 134 → 137 → 140 gram
        // Pakan harian: FR 5% × biomassa = ±7–8 kg/hari (3x pemberian)
        $beratA = 119.0;
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratA += 3.0;
            $biomassaA = 1150 * ($beratA / 1000);
            $pakanA = round($biomassaA * 0.05, 1); // FR 5%

            // Pakan dibagi: 40% Starter (rasio 2) + 60% Grower (rasio 3)
            $porsi1A = round($pakanA * 0.4, 1);
            $porsi2A = round($pakanA - $porsi1A, 1);

            DailyParameter::create([
                'kolam_id' => $kolamA->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(270, 295) / 10,
                'ph' => rand(69, 75) / 10,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => $beratA,
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamA->id,
                'rule_id' => $ruleNormal->id,
                'user_id' => $operator->id,
                'tanggal_pakan' => $date,
                'frekuensi' => 3,
                'rekomendasi_sistem' => $pakanA,
                'pakan_aktual' => $pakanA,
            ]);
            $feed
                ->details()
                ->create([
                    'inventory_id' => $pakan1->id,
                    'rasio' => 2,
                    'jumlah_kg' => $porsi1A,
                ]);
            $feed
                ->details()
                ->create([
                    'inventory_id' => $pakan2->id,
                    'rasio' => 3,
                    'jumlah_kg' => $porsi2A,
                ]);
        }

        // =========================================================================
        // KOLAM B — FASE GROWER (200–240 gram)
        //
        // Skenario: siklus ke-72 hari. Benih 20 gram ditebar 72 hari lalu.
        // Sempat ada serangan jamur Saprolegnia di hari ke-42 namun sudah teratasi.
        // Berat saat ini ±220 gram, mendekati batas atas fase Grower.
        // Feed Rate (FR) 3% dari biomassa, 100% pakan Grower.
        //
        // Populasi: 1.800 tebar − 120 mati = 1.680 ekor aktif
        // =========================================================================
        $kolamB = Kolam::create([
            'nama_kolam' => 'Kolam Beton B',
            'lokasi' => 'Blok Selatan',
            'bentuk_kolam' => 'Persegi',
            'status_kolam' => 'Aktif',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.5,
            'jumlah_ikan' => 1680,
            'berat_rata_gram' => 220.0,
        ]);

        $tanggalTebarB = Carbon::today()->subDays(72);
        SiklusBudidaya::create([
            'kolam_id' => $kolamB->id,
            'tanggal_mulai' => $tanggalTebarB,
            'status_aktif' => 'Aktif',
            'jumlah_tebar_awal' => 1800,
        ]);
        TebarLog::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $admin->id,
            'tanggal_tebar' => $tanggalTebarB,
            'jumlah_ikan' => 1800,
            'berat_rata_gram' => 20,
            'sumber_benih' => 'CV. Nila Jaya',
        ]);

        // Kematian: 40 (adaptasi) + 50 (jamur) + 30 (pasca obat) = 120 → sisa 1.680 ekor
        MortalityLog::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(65),
            'jumlah_mati' => 40,
            'catatan' => 'Kematian awal masa adaptasi, kondisi sudah stabil',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(30),
            'jumlah_mati' => 50,
            'catatan' => 'Serangan jamur Saprolegnia sp., sudah diobati dengan garam dapur dan antijamur',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(10),
            'jumlah_mati' => 30,
            'catatan' => 'Kematian pasca pengobatan jamur, kondisi kolam sudah kembali normal',
        ]);

        // Data harian 7 hari terakhir
        // Pertumbuhan: +3 gram/hari → 202 → 205 → 208 → 211 → 214 → 217 → 220 gram
        // Pakan harian: FR 3% × biomassa = ±10–11 kg/hari (3x pemberian)
        $beratB = 199.0;
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratB += 3.0;
            $biomassaB = 1680 * ($beratB / 1000);
            $pakanB = round($biomassaB * 0.03, 1); // FR 3%

            DailyParameter::create([
                'kolam_id' => $kolamB->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(275, 300) / 10,
                'ph' => rand(70, 76) / 10,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => $beratB,
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamB->id,
                'rule_id' => $ruleNormal->id,
                'user_id' => $operator->id,
                'tanggal_pakan' => $date,
                'frekuensi' => 3,
                'rekomendasi_sistem' => $pakanB,
                'pakan_aktual' => $pakanB,
            ]);
            $feed
                ->details()
                ->create([
                    'inventory_id' => $pakan2->id,
                    'rasio' => 1,
                    'jumlah_kg' => $pakanB,
                ]);
        }

        // =========================================================================
        // KOLAM C — FASE FINISHER / MENDEKATI PANEN (250 gram ke atas)
        //
        // Skenario: siklus ke-105 hari. Ini kolam terbesar. Pernah ada wabah
        // bakteri Aeromonas di hari ke-65 namun sudah tertangani. Seminggu lalu
        // dilakukan panen sortir 60 ekor untuk mengurangi kepadatan. Berat
        // saat ini ±289.5 gram, siap panen utama dalam 1–2 minggu ke depan.
        // Feed Rate (FR) 2% dari biomassa, 100% pakan Finisher.
        //
        // Populasi: 3.000 tebar − 400 mati − 60 panen parsial = 2.540 ekor aktif
        // =========================================================================
        $kolamC = Kolam::create([
            'nama_kolam' => 'Kolam Tanah C',
            'lokasi' => 'Blok Timur',
            'bentuk_kolam' => 'Persegi',
            'status_kolam' => 'Aktif',
            'panjang_m' => 20,
            'lebar_m' => 10,
            'kedalaman_m' => 1.8,
            'jumlah_ikan' => 2540,
            'berat_rata_gram' => 289.5,
        ]);

        $tanggalTebarC = Carbon::today()->subDays(105);
        SiklusBudidaya::create([
            'kolam_id' => $kolamC->id,
            'tanggal_mulai' => $tanggalTebarC,
            'status_aktif' => 'Aktif',
            'jumlah_tebar_awal' => 3000,
        ]);
        TebarLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $admin->id,
            'tanggal_tebar' => $tanggalTebarC,
            'jumlah_ikan' => 3000,
            'berat_rata_gram' => 20,
            'sumber_benih' => 'Pendederan Pak Hasan',
        ]);

        // Kematian: 80+130+150+40 = 400 ekor → setelah panen parsial sisa 2.540 ekor
        MortalityLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(100),
            'jumlah_mati' => 80,
            'catatan' => 'Kematian awal siklus, proses adaptasi kolam tanah baru diperbaiki',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(70),
            'jumlah_mati' => 130,
            'catatan' => 'Cuaca panas ekstrem, kadar oksigen terlarut turun, aerator ditambah',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(40),
            'jumlah_mati' => 150,
            'catatan' => 'Wabah bakteri Aeromonas hydrophila, ditangani dengan probiotik dan antibiotik',
        ]);
        MortalityLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(15),
            'jumlah_mati' => 40,
            'catatan' => 'Kematian sisa pasca penanganan penyakit, kondisi kolam sudah pulih sepenuhnya',
        ]);

        // Panen sortir parsial: 60 ekor @±285 gram = ±17 kg
        HarvestLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $admin->id,
            'jenis_panen' => 'Parsial',
            'tanggal_panen' => Carbon::today()->subDays(7),
            'jumlah_ikan' => 60,
            'berat_total_kg' => 17.1,
            'catatan' => 'Panen sortir ikan ukuran >285 gram untuk mengurangi kepadatan sebelum panen utama',
        ]);

        // Data harian 7 hari terakhir
        // Pertumbuhan: +2.5 gram/hari → 274.5 → 277 → 279.5 → 282 → 284.5 → 287 → 289.5 gram
        // Pakan harian: FR 2% × biomassa = ±14–15 kg/hari (2x pemberian, efisiensi finisher)
        $beratC = 272.0;
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratC += 2.5;
            $biomassaC = 2540 * ($beratC / 1000);
            $pakanC = round($biomassaC * 0.02, 1); // FR 2%

            DailyParameter::create([
                'kolam_id' => $kolamC->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(270, 290) / 10,
                'ph' => rand(68, 74) / 10,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => $beratC,
            ]);

            $feed = FeedLog::create([
                'kolam_id' => $kolamC->id,
                'rule_id' => $ruleNormal->id,
                'user_id' => $operator->id,
                'tanggal_pakan' => $date,
                'frekuensi' => 2,
                'rekomendasi_sistem' => $pakanC,
                'pakan_aktual' => $pakanC,
            ]);
            $feed
                ->details()
                ->create([
                    'inventory_id' => $pakan3->id,
                    'rasio' => 1,
                    'jumlah_kg' => $pakanC,
                ]);
        }
    }
}
