<?php

namespace Database\Seeders;

use App\Models\InferensiLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\ParameterHarian;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExampleSiklusSeeder extends Seeder
{
    public function run(): void
    {
        $supervisor = User::where('email', 'supervisor@nila.com')->firstOrFail();
        $operator = User::where('email', 'operator@nila.com')->firstOrFail();

        $mulai = Carbon::parse('2026-06-10');

        // Hapus data contoh sebelumnya jika ada (urutkan sesuai FK)
        Tiket::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        InferensiLog::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        ParameterHarian::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        MortalityLog::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        SiklusBudidaya::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        TebarLog::whereRelation('kolam', 'nama_kolam', 'Kolam A')->delete();
        DB::table('operator_kolam')
            ->whereIn('kolam_id', Kolam::where('nama_kolam', 'Kolam A')->pluck('id'))
            ->delete();
        Kolam::where('nama_kolam', 'Kolam A')->delete();

        // === KOLAM A ===
        $kolam = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Blok Timur - Area 1',
            'panjang_m' => 10.50,
            'lebar_m' => 8.00,
            'kedalaman_m' => 1.30,
            'jumlah_ikan' => 4850,
            'berat_rata_gram' => 85.00,
        ]);

        // Operator assignment
        DB::table('operator_kolam')->insert([
            'user_id' => $operator->id,
            'kolam_id' => $kolam->id,
            'tanggal_penugasan' => $mulai->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // === SIKLUS BUDIDAYA ===
        $siklus = SiklusBudidaya::create([
            'kolam_id' => $kolam->id,
            'tanggal_mulai' => $mulai->toDateString(),
            'jumlah_tebar_awal' => 5000,
            'status_aktif' => 'berjalan',
        ]);

        // === TEBAR LOG ===
        TebarLog::create([
            'kolam_id' => $kolam->id,
            'user_id' => $operator->id,
            'tanggal_tebar' => $mulai->toDateString(),
            'jumlah_ikan' => 5000,
            'berat_rata_gram' => 2.50,
            'sumber_benih' => 'Balai Benih Ikan (BBI) Punten, Batu',
        ]);

        // === MORTALITY LOGS (6 kejadian, total 150 mati) ===
        $kematian = [
            ['day' => 5,  'jumlah' => 20, 'kategori' => 'stres_lingkungan',  'catatan' => 'Ikan tampak lemas setelah penebaran.'],
            ['day' => 12, 'jumlah' => 15, 'kategori' => 'penyakit',          'catatan' => 'Ditemukan ikan dengan bercak putih di tubuh.'],
            ['day' => 18, 'jumlah' => 25, 'kategori' => 'kanibalisme',       'catatan' => 'Ikan saling memakan akibat ukuran tidak seragam.'],
            ['day' => 22, 'jumlah' => 30, 'kategori' => 'stres_lingkungan',  'catatan' => 'Amonia tinggi, ikan megap-megap di permukaan.'],
            ['day' => 27, 'jumlah' => 35, 'kategori' => 'penyakit',          'catatan' => 'Sirip ikan rontok, diduga infeksi bakteri.'],
            ['day' => 29, 'jumlah' => 25, 'kategori' => 'lainnya',           'catatan' => 'Ikan ditemukan mati tanpa gejala jelas.'],
        ];

        foreach ($kematian as $m) {
            MortalityLog::create([
                'kolam_id' => $kolam->id,
                'siklus_budidaya_id' => $siklus->id,
                'user_id' => $operator->id,
                'tanggal_kematian' => (clone $mulai)->addDays($m['day'])->toDateString(),
                'jumlah_mati' => $m['jumlah'],
                'kategori_kematian' => $m['kategori'],
                'catatan' => $m['catatan'],
            ]);
        }

        // === PARAMETER HARIAN (30 hari) + INFERENSI LOG + TIKET ===
        $dataHarian = $this->dataParameter();
        $abnormalDays = [6, 12, 18, 22, 25, 28];

        foreach ($dataHarian as $day => $params) {
            $tanggal = (clone $mulai)->addDays($day - 1);

            $param = ParameterHarian::create([
                'kolam_id' => $kolam->id,
                'user_id' => $operator->id,
                'tanggal_cek' => $tanggal->toDateString(),
                'suhu' => $params['suhu'],
                'ph' => $params['ph'],
                'do_mgl' => $params['do'],
                'amonia_mgl' => $params['amonia'],
                'flok_ml' => $params['flok'],
                'kecerahan_cm' => $params['kecerahan'],
            ]);

            // InferensiLog selalu dibuat untuk setiap parameter (sama seperti di controller)
            $fc = $this->forwardChaining($params);
            $log = InferensiLog::create([
                'kolam_id' => $kolam->id,
                'parameter_harian_id' => $param->id,
                'tanggal_inferensi' => $tanggal->toDateString(),
                'fakta_input' => $fc['fakta_input'],
                'fakta_baru' => $fc['fakta_baru'],
                'rule_terpicu' => $fc['rule_terpicu'],
                'kode_diagnosa' => $fc['kode_diagnosa'],
                'label_diagnosa' => $fc['label_diagnosa'],
                'kode_kesimpulan' => $fc['kode_kesimpulan'],
                'tindakan_mitigasi' => $fc['tindakan_mitigasi'],
                'peringatan' => $fc['peringatan'],
            ]);

            // Tiket hanya dibuat jika diagnosa tidak normal
            if (! in_array('D-NORMAL', $fc['kode_diagnosa'])) {
                $jam = rand(8, 16);
                $deadline = (clone $tanggal)->setTime($jam, rand(0, 59));
                $labelText = implode('; ', $fc['label_diagnosa']);
                $tindakanText = implode("\n", $fc['tindakan_mitigasi']);

                Tiket::create([
                    'kolam_id' => $kolam->id,
                    'inferensi_log_id' => $log->id,
                    'operator_id' => $operator->id,
                    'supervisor_id' => ($day <= 18) ? $supervisor->id : null,
                    'judul' => 'Mitigasi: '.$labelText,
                    'deskripsi_tindakan' => $tindakanText,
                    'status' => $this->ticketStatus($day),
                    'deadline' => (clone $deadline)->addHours(3),
                    'diselesaikan_at' => ($day <= 18) ? (clone $deadline)->addDay() : null,
                    'diverifikasi_at' => ($day <= 12) ? (clone $deadline)->addDays(2) : null,
                    'catatan_supervisor' => $day === 6 ? 'Tindakan sesuai SOP. Lanjutkan pemantauan rutin.' : null,
                ]);
            }
        }
    }

    private function ticketStatus(int $day): string
    {
        return match (true) {
            $day <= 6 => 'selesai',
            $day <= 12 => 'menunggu_verifikasi',
            $day <= 18 => 'selesai',
            $day <= 22 => 'in_progress',
            default => 'open',
        };
    }

    private function dataParameter(): array
    {
        // Hari normal: semua parameter dalam rentang optimal → fakta = [F19,F22,F25,F28,F31,F34]
        //   → tidak ada rule terpicu → D-NORMAL
        // Hari abnormal: satu atau lebih parameter keluar batas sehingga rule terpicu
        return [
            1 => ['suhu' => 27.5, 'ph' => 7.2, 'do' => 5.2, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 35.0],
            2 => ['suhu' => 28.0, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 34.0],
            3 => ['suhu' => 27.8, 'ph' => 7.1, 'do' => 5.3, 'amonia' => 0.00, 'flok' => 17.0, 'kecerahan' => 33.0],
            4 => ['suhu' => 28.2, 'ph' => 7.4, 'do' => 5.1, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 33.0],
            5 => ['suhu' => 28.5, 'ph' => 7.5, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 32.0],
            6 => ['suhu' => 28.0, 'ph' => 8.6, 'do' => 5.5, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 32.0], // F23 → D03 pH
            7 => ['suhu' => 27.5, 'ph' => 7.3, 'do' => 5.4, 'amonia' => 0.00, 'flok' => 19.0, 'kecerahan' => 33.0],
            8 => ['suhu' => 27.0, 'ph' => 7.2, 'do' => 5.5, 'amonia' => 0.00, 'flok' => 20.0, 'kecerahan' => 34.0],
            9 => ['suhu' => 27.8, 'ph' => 7.4, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 20.0, 'kecerahan' => 33.0],
            10 => ['suhu' => 28.0, 'ph' => 7.3, 'do' => 5.2, 'amonia' => 0.00, 'flok' => 21.0, 'kecerahan' => 32.0],
            11 => ['suhu' => 27.5, 'ph' => 7.2, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 20.0, 'kecerahan' => 31.0],
            12 => ['suhu' => 27.0, 'ph' => 7.2, 'do' => 4.2, 'amonia' => 0.00, 'flok' => 16.0, 'kecerahan' => 33.0], // F26 → D05 aerasi
            13 => ['suhu' => 27.5, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 32.0],
            14 => ['suhu' => 28.0, 'ph' => 7.4, 'do' => 5.1, 'amonia' => 0.00, 'flok' => 19.0, 'kecerahan' => 33.0],
            15 => ['suhu' => 28.5, 'ph' => 7.5, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 20.0, 'kecerahan' => 32.0],
            16 => ['suhu' => 29.0, 'ph' => 7.4, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 21.0, 'kecerahan' => 31.0],
            17 => ['suhu' => 28.5, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 22.0, 'kecerahan' => 30.0],
            18 => ['suhu' => 28.0, 'ph' => 7.5, 'do' => 3.8, 'amonia' => 0.08, 'flok' => 22.0, 'kecerahan' => 28.0], // F30+F26+F19+F35 → D07 limbah
            19 => ['suhu' => 28.0, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 23.0, 'kecerahan' => 30.0],
            20 => ['suhu' => 28.5, 'ph' => 7.4, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 24.0, 'kecerahan' => 30.0],
            21 => ['suhu' => 29.0, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 25.0, 'kecerahan' => 30.0],
            22 => ['suhu' => 28.0, 'ph' => 7.3, 'do' => 2.5, 'amonia' => 0.15, 'flok' => 28.0, 'kecerahan' => 28.0], // F27+F30 → D08 kolaps
            23 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 24.0, 'kecerahan' => 30.0],
            24 => ['suhu' => 27.5, 'ph' => 7.3, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 33.0],
            25 => ['suhu' => 28.0, 'ph' => 7.4, 'do' => 5.5, 'amonia' => 0.00, 'flok' => 10.0, 'kecerahan' => 42.0], // F32+F28+F25+F36 → D09 flok
            26 => ['suhu' => 28.5, 'ph' => 7.5, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 16.0, 'kecerahan' => 35.0],
            27 => ['suhu' => 29.0, 'ph' => 7.4, 'do' => 5.2, 'amonia' => 0.00, 'flok' => 18.0, 'kecerahan' => 34.0],
            28 => ['suhu' => 32.0, 'ph' => 7.5, 'do' => 5.5, 'amonia' => 0.00, 'flok' => 20.0, 'kecerahan' => 35.0], // F20 → D01 suhu
            29 => ['suhu' => 30.0, 'ph' => 7.4, 'do' => 5.0, 'amonia' => 0.00, 'flok' => 22.0, 'kecerahan' => 34.0],
            30 => ['suhu' => 29.0, 'ph' => 7.3, 'do' => 5.3, 'amonia' => 0.00, 'flok' => 25.0, 'kecerahan' => 33.0],
        ];
    }

    private function forwardChaining(array $p): array
    {
        $input = [
            'suhu' => $p['suhu'],
            'ph' => $p['ph'],
            'do' => $p['do'],
            'amonia' => $p['amonia'],
            'flok' => $p['flok'],
            'kecerahan' => $p['kecerahan'],
        ];

        $fakta = $this->evaluasiFakta($input);
        $hasil = $this->cocokkanRule($fakta);

        return [
            'fakta_input' => $input,
            'fakta_baru' => $fakta,
            'rule_terpicu' => $hasil['rule_terpicu'],
            'kode_diagnosa' => $hasil['kode_diagnosa'],
            'label_diagnosa' => $hasil['label_diagnosa'],
            'kode_kesimpulan' => $hasil['kode_kesimpulan'],
            'tindakan_mitigasi' => $hasil['tindakan_mitigasi'],
            'peringatan' => $hasil['peringatan'],
        ];
    }

    private function evaluasiFakta(array $input): array
    {
        $fakta = [];

        // Suhu: F19=F20=F21
        if ($input['suhu'] >= 25.0 && $input['suhu'] <= 30.0) {
            $fakta[] = 'F19';
        } elseif (($input['suhu'] >= 20.0 && $input['suhu'] <= 24.99) || ($input['suhu'] >= 30.01 && $input['suhu'] <= 34.0)) {
            $fakta[] = 'F20';
        } elseif ($input['suhu'] < 20.0 || $input['suhu'] > 34.0) {
            $fakta[] = 'F21';
        }

        // pH: F22=F23=F24
        if ($input['ph'] >= 6.5 && $input['ph'] <= 8.5) {
            $fakta[] = 'F22';
        } elseif (($input['ph'] >= 6.0 && $input['ph'] <= 6.49) || ($input['ph'] >= 8.51 && $input['ph'] <= 9.0)) {
            $fakta[] = 'F23';
        } elseif ($input['ph'] < 6.0 || $input['ph'] > 9.0) {
            $fakta[] = 'F24';
        }

        // DO: F25=F26=F27
        if ($input['do'] >= 5.0) {
            $fakta[] = 'F25';
        } elseif ($input['do'] >= 3.0 && $input['do'] <= 4.99) {
            $fakta[] = 'F26';
        } elseif ($input['do'] < 3.0) {
            $fakta[] = 'F27';
        }

        // Amonia: F28=F29=F30
        if ($input['amonia'] < 0.01) {
            $fakta[] = 'F28';
        } elseif ($input['amonia'] >= 0.01 && $input['amonia'] <= 0.05) {
            $fakta[] = 'F29';
        } elseif ($input['amonia'] > 0.05) {
            $fakta[] = 'F30';
        }

        // Flok: F31=F32=F33
        if ($input['flok'] >= 15.0 && $input['flok'] <= 30.0) {
            $fakta[] = 'F31';
        } elseif ($input['flok'] < 15.0) {
            $fakta[] = 'F32';
        } elseif ($input['flok'] > 30.0) {
            $fakta[] = 'F33';
        }

        // Kecerahan: F34=F35=F36
        if ($input['kecerahan'] >= 30.0 && $input['kecerahan'] <= 40.0) {
            $fakta[] = 'F34';
        } elseif ($input['kecerahan'] < 30.0) {
            $fakta[] = 'F35';
        } elseif ($input['kecerahan'] > 40.0) {
            $fakta[] = 'F36';
        }

        return $fakta;
    }

    private function cocokkanRule(array $fakta): array
    {
        $cekFakta = function ($syarat) use ($fakta) {
            return count(array_intersect($syarat, $fakta)) === count($syarat);
        };

        $ruleCodes = [];
        $kodeD = [];
        $labelD = [];
        $kodeK = [];
        $tindakan = [];
        $peringatan = [];

        if ($cekFakta(['F21'])) {
            $ruleCodes[] = 'D02';
            $kodeD[] = 'D02';
            $labelD[] = 'Stres Suhu Kritis';
            $kodeK[] = 'K02';
            $tindakan[] = 'Hentikan pakan sementara; ganti air parsial 20–30% dengan air bersuhu sesuai';
            $peringatan[] = 'PERINGATAN: Suhu kritis. Risiko kematian ikan jika tidak segera ditangani.';
        }

        if ($cekFakta(['F35', 'F24', 'F31'])) {
            $ruleCodes[] = 'D12';
            $kodeD[] = 'D12';
            $labelD[] = 'Blooming Fitoplankton Berlebih';
            $kodeK[] = 'K12';
            $tindakan[] = 'Hentikan pemupukan; ganti air permukaan 20%; kurangi intensitas cahaya matahari langsung dengan paranet';
            $peringatan[] = 'PERINGATAN: Blooming fitoplankton berlebih. Kecerahan air menurun drastis.';
        }

        if ($cekFakta(['F24'])) {
            $ruleCodes[] = 'D04';
            $kodeD[] = 'D04';
            $labelD[] = 'pH Ekstrem Kritis';
            $kodeK[] = 'K04';
            $tindakan[] = 'Hentikan pakan; ganti air masif 40–50%; tambahkan buffer pH';
            $peringatan[] = 'DARURAT: pH kritis. Hentikan pakan dan lakukan koreksi air sekarang.';
        }

        if ($cekFakta(['F27', 'F30'])) {
            $ruleCodes[] = 'D08';
            $kodeD[] = 'D08';
            $labelD[] = 'Kolaps Bioflok';
            $kodeK[] = 'K08';
            $tindakan[] = 'Hentikan pakan segera; ganti air 50%; aktifkan aerasi penuh; tambah probiotik dan molase';
            $peringatan[] = 'DARURAT: Kolaps bioflok. DO kritis dan amonia sangat tinggi. Tindakan darurat diperlukan.';
        }

        if ($cekFakta(['F32', 'F30'])) {
            $ruleCodes[] = 'D11';
            $kodeD[] = 'D11';
            $labelD[] = 'Flok Kolaps Akibat Amonia Tinggi';
            $kodeK[] = 'K11';
            $tindakan[] = 'Hentikan pakan segera; ganti air 40–50%; tambah probiotik dan molase untuk bangun ulang flok';
            $peringatan[] = 'DARURAT: Flok kolaps akibat amonia tinggi. Sistem bioflok tidak berfungsi.';
        }

        if ($cekFakta(['F30', 'F26', 'F19', 'F35'])) {
            $ruleCodes[] = 'D07';
            $kodeD[] = 'D07';
            $labelD[] = 'Akumulasi Limbah Berlebih';
            $kodeK[] = 'K07';
            $tindakan[] = 'Ganti air 30–40%; siphon dasar kolam; tambah probiotik; kurangi dosis pakan 25%';
            $peringatan[] = 'PERINGATAN: Akumulasi limbah tinggi. Lakukan pergantian air dan pembersihan segera.';
        }

        if ($cekFakta(['F33', 'F26', 'F35'])) {
            $ruleCodes[] = 'D10';
            $kodeD[] = 'D10';
            $labelD[] = 'Flok Berlebih — Menguras Oksigen';
            $kodeK[] = 'K10';
            $tindakan[] = 'Siphon endapan flok dari dasar kolam; kurangi pakan; ganti air parsial 20–30%';
            $peringatan[] = 'PERINGATAN: Flok berlebih menyebabkan DO turun. Lakukan pengelolaan flok segera.';
        }

        if ($cekFakta(['F20', 'F22', 'F25', 'F28', 'F34'])) {
            $ruleCodes[] = 'D01';
            $kodeD[] = 'D01';
            $labelD[] = 'Stres Suhu Ringan';
            $kodeK[] = 'K01';
            $tindakan[] = 'Tutupi kolam dengan paranet jika suhu panas; tambah aerasi untuk pendinginan';
            $peringatan[] = 'Suhu tidak ideal. Pantau setiap 2 jam.';
        }

        if ($cekFakta(['F23', 'F19', 'F25', 'F28', 'F34'])) {
            $ruleCodes[] = 'D03';
            $kodeD[] = 'D03';
            $labelD[] = 'Gangguan pH';
            $kodeK[] = 'K03';
            $tindakan[] = 'Tambahkan kapur (pH rendah) atau ganti air parsial 10–20% (pH tinggi)';
            $peringatan[] = 'pH tidak ideal. Lakukan koreksi dan pantau ulang dalam 3 jam.';
        }

        if ($cekFakta(['F26', 'F19', 'F28', 'F31', 'F34'])) {
            $ruleCodes[] = 'D05';
            $kodeD[] = 'D05';
            $labelD[] = 'Aerasi Kurang';
            $kodeK[] = 'K05';
            $tindakan[] = 'Periksa dan perbaiki pompa udara/kincir; tambah titik aerasi';
            $peringatan[] = 'DO rendah karena aerasi kurang. Periksa sistem aerasi segera.';
        }

        if ($cekFakta(['F29', 'F19', 'F25', 'F31', 'F34'])) {
            $ruleCodes[] = 'D06';
            $kodeD[] = 'D06';
            $labelD[] = 'Overfeeding';
            $kodeK[] = 'K06';
            $tindakan[] = 'Hentikan pakan 1–2 hari; siphon sisa pakan di dasar kolam; ganti air 20–30%';
            $peringatan[] = 'Amonia meningkat akibat sisa pakan berlebih. Hentikan pakan dan bersihkan kolam.';
        }

        if ($cekFakta(['F32', 'F28', 'F25', 'F36'])) {
            $ruleCodes[] = 'D09';
            $kodeD[] = 'D09';
            $labelD[] = 'Flok Belum Terbentuk / Bakteri Rendah';
            $kodeK[] = 'K09';
            $tindakan[] = 'Tambahkan molase/sumber karbon untuk memacu bakteri; tambah probiotik starter bioflok';
            $peringatan[] = 'Flok terlalu rendah. Sistem bioflok belum optimal. Tambahkan sumber karbon segera.';
        }

        if (count($kodeD) === 0) {
            return [
                'rule_terpicu' => ['D-NORMAL'],
                'kode_diagnosa' => ['D-NORMAL'],
                'label_diagnosa' => ['Kondisi Air Optimal (Normal)'],
                'kode_kesimpulan' => ['K-NORMAL'],
                'tindakan_mitigasi' => ['Lanjutkan SOP pemeliharaan dan pemberian pakan seperti biasa. Tidak ada tindakan darurat.'],
                'peringatan' => ['Aman. Kualitas air terkendali.'],
            ];
        }

        return [
            'rule_terpicu' => $ruleCodes,
            'kode_diagnosa' => $kodeD,
            'label_diagnosa' => $labelD,
            'kode_kesimpulan' => $kodeK,
            'tindakan_mitigasi' => $tindakan,
            'peringatan' => $peringatan,
        ];
    }
}
