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
        // PEMBARUAN: setiap jenis pakan diseragamkan stoknya menjadi 150 kg.
        // =========================================================================
        $stokSeragamKg = 150;

        $pakan1 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-1 (Starter)',
            'total_stok_kg' => $stokSeragamKg,
            'terakhir_update' => now(),
        ]);
        $pakan2 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-2 (Grower)',
            'total_stok_kg' => $stokSeragamKg,
            'terakhir_update' => now(),
        ]);
        $pakan3 = Inventory::create([
            'nama_pakan' => 'Hi-Pro-Vite 781-3 (Finisher)',
            'total_stok_kg' => $stokSeragamKg,
            'terakhir_update' => now(),
        ]);

        InventoryLog::insert([
            [
                'inventory_id' => $pakan1->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => $stokSeragamKg,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'inventory_id' => $pakan2->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => $stokSeragamKg,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'inventory_id' => $pakan3->id,
                'user_id' => $admin->id,
                'tipe' => 'Masuk',
                'jumlah' => $stokSeragamKg,
                'keterangan' => 'Pembelian stok awal bulan April',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =========================================================================
        // KOLAM A — FASE STARTER → GROWER AWAL
        //
        // PEMBARUAN: berat awal tebar 85 gram (seragam), berat saat ini 110 gram.
        // Siklus tetap 40 hari. Feed Rate (FR) 5% dari biomassa, pakan dicampur
        // 40% Starter + 60% Grower.
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
            'berat_rata_gram' => 110.0,
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
            'berat_rata_gram' => 85,
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
        // PEMBARUAN: pertumbuhan dihitung ulang agar konsisten dengan
        // berat awal 85 gram -> berat saat ini 110 gram selama 40 hari.
        // Laju harian = (110 - 85) / 40 = 0.625 gram/hari.
        // Berat 7 hari lalu = 110 - (7 x 0.625) = 105.625 gram.
        // Pakan harian: FR 5% x biomassa (3x pemberian)
        $lajuPertumbuhanA = (110.0 - 85.0) / 40; // gram/hari
        $beratA = 110.0 - (7 * $lajuPertumbuhanA);
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratA += $lajuPertumbuhanA;
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
                'berat_sample' => round($beratA, 1),
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
        // KOLAM B — FASE GROWER
        //
        // PEMBARUAN: berat saat ini 200 gram (berat awal tebar tetap dicatat
        // 85 gram secara konseptual untuk konsistensi laju pertumbuhan, namun
        // TIDAK ada TebarLog yang dibuat untuk kolam ini sesuai permintaan).
        // Siklus tetap 72 hari. Sempat ada serangan jamur Saprolegnia, sudah
        // teratasi. Feed Rate (FR) 3% dari biomassa, 100% pakan Grower.
        //
        // Populasi: 1.800 awal − 120 mati = 1.680 ekor aktif
        // (dicatat di SiklusBudidaya saja, tanpa TebarLog)
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
            'berat_rata_gram' => 200.0,
        ]);

        $tanggalTebarB = Carbon::today()->subDays(72);
        SiklusBudidaya::create([
            'kolam_id' => $kolamB->id,
            'tanggal_mulai' => $tanggalTebarB,
            'status_aktif' => 'Aktif',
            'jumlah_tebar_awal' => 1800,
        ]);
        // CATATAN: TebarLog sengaja TIDAK dibuat untuk Kolam B sesuai permintaan.
        // Populasi awal tetap tercatat lewat SiklusBudidaya->jumlah_tebar_awal di atas.

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
        // PEMBARUAN: pertumbuhan dihitung ulang agar konsisten dengan
        // berat awal konseptual 85 gram -> berat saat ini 200 gram selama 72 hari.
        // Laju harian = (200 - 85) / 72 = 1.597 gram/hari.
        // Pakan harian: FR 3% x biomassa (3x pemberian)
        $lajuPertumbuhanB = (200.0 - 85.0) / 72; // gram/hari
        $beratB = 200.0 - (7 * $lajuPertumbuhanB);
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratB += $lajuPertumbuhanB;
            $biomassaB = 1680 * ($beratB / 1000);
            $pakanB = round($biomassaB * 0.03, 1); // FR 3%

            DailyParameter::create([
                'kolam_id' => $kolamB->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(275, 300) / 10,
                'ph' => rand(70, 76) / 10,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => round($beratB, 1),
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
        // KOLAM C — FASE FINISHER / MENDEKATI PANEN
        //
        // PEMBARUAN: berat saat ini 250 gram (berat awal tebar tetap dicatat
        // 85 gram secara konseptual untuk konsistensi laju pertumbuhan, namun
        // TIDAK ada TebarLog yang dibuat untuk kolam ini sesuai permintaan).
        // Siklus tetap 105 hari. Pernah ada wabah Aeromonas, sudah tertangani.
        // Seminggu lalu dilakukan panen sortir 60 ekor. Feed Rate (FR) 2% dari
        // biomassa, 100% pakan Finisher.
        //
        // Populasi: 3.000 awal − 400 mati − 60 panen parsial = 2.540 ekor aktif
        // (dicatat di SiklusBudidaya saja, tanpa TebarLog)
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
            'berat_rata_gram' => 250.0,
        ]);

        $tanggalTebarC = Carbon::today()->subDays(105);
        SiklusBudidaya::create([
            'kolam_id' => $kolamC->id,
            'tanggal_mulai' => $tanggalTebarC,
            'status_aktif' => 'Aktif',
            'jumlah_tebar_awal' => 3000,
        ]);
        // CATATAN: TebarLog sengaja TIDAK dibuat untuk Kolam C sesuai permintaan.
        // Populasi awal tetap tercatat lewat SiklusBudidaya->jumlah_tebar_awal di atas.

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

        // Panen sortir parsial: 60 ekor @±245 gram = ±14.7 kg
        // (disesuaikan dengan berat saat ini 250 gram, bukan 289.5 gram seperti sebelumnya)
        HarvestLog::create([
            'kolam_id' => $kolamC->id,
            'user_id' => $admin->id,
            'jenis_panen' => 'Parsial',
            'tanggal_panen' => Carbon::today()->subDays(7),
            'jumlah_ikan' => 60,
            'berat_total_kg' => 14.7,
            'catatan' => 'Panen sortir ikan ukuran besar untuk mengurangi kepadatan sebelum panen utama',
        ]);

        // Data harian 7 hari terakhir
        // PEMBARUAN: pertumbuhan dihitung ulang agar konsisten dengan
        // berat awal konseptual 85 gram -> berat saat ini 250 gram selama 105 hari.
        // Laju harian = (250 - 85) / 105 = 1.571 gram/hari.
        // Pakan harian: FR 2% x biomassa (2x pemberian, efisiensi finisher)
        $lajuPertumbuhanC = (250.0 - 85.0) / 105; // gram/hari
        $beratC = 250.0 - (7 * $lajuPertumbuhanC);
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $beratC += $lajuPertumbuhanC;
            $biomassaC = 2540 * ($beratC / 1000);
            $pakanC = round($biomassaC * 0.02, 1); // FR 2%

            DailyParameter::create([
                'kolam_id' => $kolamC->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(270, 290) / 10,
                'ph' => rand(68, 74) / 10,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => round($beratC, 1),
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