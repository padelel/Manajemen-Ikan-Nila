<?php

namespace Database\Seeders;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\ParameterHarian;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\User;
use App\Services\ForwardChainingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // 1. RESET DATABASE (MENGOSONGKAN TABEL BARU)
        // =========================================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Kolam::truncate();
        DB::table('operator_kolam')->truncate();
        SiklusBudidaya::truncate();
        TebarLog::truncate();
        MortalityLog::truncate();
        HarvestLog::truncate();
        ParameterHarian::truncate();
        DB::table('inferensi_logs')->truncate();
        DB::table('tikets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Instansiasi Mesin Inferensi (AI) untuk digunakan di seeder
        $fcService = new ForwardChainingService;

        // =========================================================================
        // 2. PENGGUNA (SUPERVISOR & OPERATOR)
        // =========================================================================
        $supervisor = User::create([
            'name' => 'Bapak Supervisor',
            'email' => 'supervisor@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'email_verified_at' => now(),
        ]);

        $operator = User::create([
            'name' => 'Mas Operator Lapangan',
            'email' => 'operator@nila.com',
            'password' => Hash::make('password123'),
            'role' => 'operator',
            'email_verified_at' => now(),
        ]);

        // =========================================================================
        // 3. KOLAM A — FASE AWAL SIKLUS (NORMAL)
        // =========================================================================
        $kolamA = Kolam::create([
            'nama_kolam' => 'Kolam Terpal A',
            'lokasi' => 'Blok Utara',
            'panjang_m' => 4.0,
            'lebar_m' => 4.0,
            'kedalaman_m' => 1.1,
            'status_kolam' => 'aktif',
        ]);
        $operator->kolams()->attach($kolamA->id, ['tanggal_penugasan' => Carbon::today()->subDays(40)]);

        $siklusA = SiklusBudidaya::create([
            'kolam_id' => $kolamA->id,
            'tanggal_mulai' => Carbon::today()->subDays(40),
            'jumlah_tebar_awal' => 1200,
            'status_aktif' => 'berjalan',
        ]);

        TebarLog::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $supervisor->id,
            'tanggal_tebar' => Carbon::today()->subDays(40),
            'jumlah_ikan' => 1200,
            'berat_rata_gram' => 85,
            'sumber_benih' => 'Balai Benih Ikan Lokal',
        ]);

        MortalityLog::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(35),
            'jumlah_mati' => 30,
            'catatan' => 'Kematian masa adaptasi awal, kondisi air belum stabil sepenuhnya',
        ]);

        // =========================================================================
        // 4. KOLAM B — SIKLUS PERTENGAHAN (KITA BUAT SIMULASI KASUS AI DI SINI)
        // Catatan: Sesuai aturan lama, Kolam B & C tidak ada TebarLog.
        // =========================================================================
        $kolamB = Kolam::create([
            'nama_kolam' => 'Kolam Beton B',
            'lokasi' => 'Blok Selatan',
            'panjang_m' => 10.0,
            'lebar_m' => 5.0,
            'kedalaman_m' => 1.1,
            'status_kolam' => 'aktif',
        ]);
        $operator->kolams()->attach($kolamB->id, ['tanggal_penugasan' => Carbon::today()->subDays(72)]);

        $siklusB = SiklusBudidaya::create([
            'kolam_id' => $kolamB->id,
            'tanggal_mulai' => Carbon::today()->subDays(72),
            'jumlah_tebar_awal' => 1800,
            'status_aktif' => 'berjalan',
        ]);

        MortalityLog::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $operator->id,
            'tanggal_kematian' => Carbon::today()->subDays(30),
            'jumlah_mati' => 50,
            'catatan' => 'Serangan jamur Saprolegnia sp., sudah diobati dengan garam',
        ]);

        // =========================================================================
        // 5. KOLAM C — FASE MENDEKATI PANEN (ADA HISTORI PANEN PARSIAL)
        // =========================================================================
        $kolamC = Kolam::create([
            'nama_kolam' => 'Kolam Tanah C',
            'lokasi' => 'Blok Timur',
            'panjang_m' => 20.0,
            'lebar_m' => 10.0,
            'kedalaman_m' => 1.1,
            'status_kolam' => 'aktif',
        ]);
        $operator->kolams()->attach($kolamC->id, ['tanggal_penugasan' => Carbon::today()->subDays(105)]);

        $siklusC = SiklusBudidaya::create([
            'kolam_id' => $kolamC->id,
            'tanggal_mulai' => Carbon::today()->subDays(105),
            'jumlah_tebar_awal' => 3000,
            'status_aktif' => 'berjalan',
        ]);

        HarvestLog::create([
            'kolam_id' => $kolamC->id,
            'siklus_budidaya_id' => $siklusC->id,
            'user_id' => $supervisor->id,
            'jenis_panen' => 'total',
            'tanggal_panen' => Carbon::today()->subDays(7),
            'jumlah_ikan_panen' => 60,
            'berat_total_kg' => 14.7,
            'catatan' => 'Panen sortir ikan ukuran besar',
        ]);

        // =========================================================================
        // 6. GENERASI PARAMETER AIR & TRIGGER FORWARD CHAINING AI (7 Hari Terakhir)
        // =========================================================================
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Kolam A: Kondisi selalu NORMAL
            $paramA = ParameterHarian::create([
                'kolam_id' => $kolamA->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(260, 290) / 10,
                'ph' => rand(70, 80) / 10,
                'do_mgl' => rand(50, 60) / 10,
                'amonia_mgl' => 0.00,
                'flok_ml' => rand(20, 25),
                'kecerahan_cm' => 35,
            ]);
            $fcService->prosesInferensi($paramA); // Cek AI (Akan Normal)

            // Kolam B: Kita buat ada masalah (Akumulasi Limbah / Amonia Tinggi) 2 hari yang lalu
            $isAnomalyB = ($i == 2); // Kejadian 2 hari lalu
            $paramB = ParameterHarian::create([
                'kolam_id' => $kolamB->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => 28.5, // F16 (Suhu ideal)
                'ph' => 7.5,
                'do_mgl' => $isAnomalyB ? 3.5 : 5.5, // F23 (DO Turun jika anomali)
                'amonia_mgl' => $isAnomalyB ? 0.08 : 0.01, // F27 (Amonia Bahaya jika anomali)
                'flok_ml' => 20,
                'kecerahan_cm' => 35,
            ]);
            $fcService->prosesInferensi($paramB); // AI akan otomatis menerbitkan Tiket untuk Rule R07 jika $isAnomalyB = true!

            // Kolam C: Kondisi selalu NORMAL
            $paramC = ParameterHarian::create([
                'kolam_id' => $kolamC->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $date,
                'suhu' => rand(270, 295) / 10,
                'ph' => rand(70, 78) / 10,
                'do_mgl' => rand(55, 65) / 10,
                'amonia_mgl' => 0.01,
                'flok_ml' => rand(15, 25),
                'kecerahan_cm' => 30,
            ]);
            $fcService->prosesInferensi($paramC); // Cek AI (Akan Normal)
        }
    }
}
