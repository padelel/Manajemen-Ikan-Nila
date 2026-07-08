<?php

namespace App\Services;

use App\Models\InferensiLog;
use App\Models\Tiket;
use App\Models\ParameterHarian;
use Carbon\Carbon;

class ForwardChainingService
{
    /**
     * FUNGSI UTAMA: Dieksekusi oleh Controller saat Operator menyimpan data.
     */
    public function prosesInferensi(ParameterHarian $parameter)
    {
        // 1. Ambil nilai mentah (Fakta Dasar)
        $faktaInput = [
            'suhu'      => (float) $parameter->suhu,
            'ph'        => (float) $parameter->ph,
            'do'        => (float) $parameter->do_mgl,
            'amonia'    => (float) $parameter->amonia_mgl,
            'flok'      => (float) $parameter->flok_ml,
            'kecerahan' => (float) $parameter->kecerahan_cm,
        ];
        
        // 2. LAPISAN 1: Evaluasi menjadi Fakta Linguistik (F16 - F33)
        $faktaBaru = $this->evaluasiFakta($faktaInput);

        // 3. LAPISAN 2: Pencocokan dengan Rule Base (R01 - R12)
        $hasilDiagnosa = $this->cocokkanRule($faktaBaru);

        // 4. LAPISAN 3: Simpan Riwayat Penarikan Kesimpulan (Inferensi Log)
        $log = InferensiLog::create([
            'kolam_id'            => $parameter->kolam_id,
            'parameter_harian_id' => $parameter->id,
            'tanggal_inferensi'   => Carbon::now()->toDateString(),
            'fakta_input'         => $faktaInput,
            'fakta_baru'          => $faktaBaru,
            'rule_terpicu'        => $hasilDiagnosa['rule_terpicu'],
            'kode_diagnosa'       => $hasilDiagnosa['kode_diagnosa'],
            'label_diagnosa'      => $hasilDiagnosa['label_diagnosa'],
            'kode_kesimpulan'     => $hasilDiagnosa['kode_kesimpulan'],
            'tindakan_mitigasi'   => $hasilDiagnosa['tindakan_mitigasi'],
            'peringatan'          => $hasilDiagnosa['peringatan']
        ]);

        // 5. AUTO-GENERATE TIKET: Jika air tidak normal, buat tiket tugas untuk Operator
        if ($hasilDiagnosa['kode_diagnosa'] !== 'D-NORMAL') {
            Tiket::create([
                'kolam_id'           => $parameter->kolam_id,
                'inferensi_log_id'   => $log->id,
                'operator_id'        => $parameter->user_id, // Ditugaskan ke operator yang mengecek
                'judul'              => 'Mitigasi Darurat: ' . $hasilDiagnosa['label_diagnosa'],
                'deskripsi_tindakan' => $hasilDiagnosa['tindakan_mitigasi'],
                'status'             => 'open',
                'deadline'           => Carbon::now()->addHours(3), // Waktu maksimal pengerjaan 3 jam
            ]);
        }

        return $log;
    }

    /**
     * LAPISAN 1: Menerjemahkan angka menjadi Fakta (Fuzzifikasi)
     */
    private function evaluasiFakta(array $input): array
    {
        $fakta = [];

        // Evaluasi Suhu (F16, F17, F18)
        if ($input['suhu'] >= 25.0 && $input['suhu'] <= 30.0) { $fakta[] = 'F16'; }
        elseif (($input['suhu'] >= 20.0 && $input['suhu'] < 25.0) || ($input['suhu'] > 30.0 && $input['suhu'] <= 33.0)) { $fakta[] = 'F17'; }
        elseif ($input['suhu'] < 20.0 || $input['suhu'] > 33.0) { $fakta[] = 'F18'; }

        // Evaluasi pH (F19, F20, F21)
        if ($input['ph'] >= 6.5 && $input['ph'] <= 8.5) { $fakta[] = 'F19'; }
        elseif (($input['ph'] >= 6.0 && $input['ph'] < 6.5) || ($input['ph'] > 8.5 && $input['ph'] <= 9.0)) { $fakta[] = 'F20'; }
        elseif ($input['ph'] < 6.0 || $input['ph'] > 9.0) { $fakta[] = 'F21'; }

        // Evaluasi DO (F22, F23, F24)
        if ($input['do'] >= 5.0) { $fakta[] = 'F22'; }
        elseif ($input['do'] >= 3.0 && $input['do'] < 5.0) { $fakta[] = 'F23'; }
        elseif ($input['do'] < 3.0) { $fakta[] = 'F24'; }

        // Evaluasi Amonia (F25, F26, F27)
        if ($input['amonia'] <= 0.0) { $fakta[] = 'F25'; }
        elseif ($input['amonia'] >= 0.01 && $input['amonia'] <= 0.05) { $fakta[] = 'F26'; }
        elseif ($input['amonia'] > 0.05) { $fakta[] = 'F27'; }

        // Evaluasi Flok (F28, F29, F30)
        if ($input['flok'] >= 15.0 && $input['flok'] <= 30.0) { $fakta[] = 'F28'; }
        elseif ($input['flok'] < 15.0) { $fakta[] = 'F29'; }
        elseif ($input['flok'] > 30.0) { $fakta[] = 'F30'; }

        // Evaluasi Kecerahan (F31, F32, F33)
        if ($input['kecerahan'] >= 30.0 && $input['kecerahan'] <= 40.0) { $fakta[] = 'F31'; }
        elseif ($input['kecerahan'] < 30.0) { $fakta[] = 'F32'; }
        elseif ($input['kecerahan'] > 40.0) { $fakta[] = 'F33'; }

        return $fakta;
    }

    /**
     * LAPISAN 2 & 3: Mesin Inferensi Rule Base
     */
    private function cocokkanRule(array $fakta): array
    {
        // Fungsi pembantu: Cek apakah SEMUA syarat fakta di dalam array terpenuhi
        $cekFakta = function($syarat) use ($fakta) {
            return count(array_intersect($syarat, $fakta)) === count($syarat);
        };

        // R01: Stres suhu ringan
        if ($cekFakta(['F17', 'F22', 'F25'])) {
            return $this->formatHasil('R01', 'D01', 'Stres suhu ringan', 'K01', 'Tutupi kolam dengan paranet jika suhu panas; tambah aerasi untuk pendinginan.', 'Suhu tidak ideal. Pantau setiap 2 jam.');
        }
        // R02: Stres suhu kritis
        if ($cekFakta(['F18'])) {
            return $this->formatHasil('R02', 'D02', 'Stres suhu kritis', 'K02', 'Hentikan pakan sementara; ganti air parsial 20–30% dengan air bersuhu sesuai.', 'PERINGATAN: Suhu kritis. Risiko kematian ikan jika tidak segera ditangani.');
        }
        // R03: Gangguan pH
        if ($cekFakta(['F20', 'F22', 'F25'])) {
            return $this->formatHasil('R03', 'D03', 'Gangguan pH', 'K03', 'Tambahkan kapur (pH rendah) atau ganti air parsial 10–20% (pH tinggi).', 'pH tidak ideal. Lakukan koreksi dan pantau ulang dalam 3 jam.');
        }
        // R04: pH ekstrem kritis
        if ($cekFakta(['F21'])) {
            return $this->formatHasil('R04', 'D04', 'pH ekstrem kritis', 'K04', 'Hentikan pakan; ganti air masif 40–50%; tambahkan buffer pH.', 'DARURAT: pH kritis. Hentikan pakan dan lakukan koreksi air sekarang.');
        }
        // R05: Aerasi kurang
        if ($cekFakta(['F23', 'F25', 'F16'])) {
            return $this->formatHasil('R05', 'D05', 'Aerasi kurang', 'K05', 'Periksa dan perbaiki pompa udara/kincir; tambah titik aerasi.', 'DO rendah karena aerasi kurang. Periksa sistem aerasi segera.');
        }
        // R06: Overfeeding
        if ($cekFakta(['F26', 'F22', 'F16'])) {
            return $this->formatHasil('R06', 'D06', 'Overfeeding', 'K06', 'Hentikan pakan 1–2 hari; siphon sisa pakan di dasar kolam; ganti air 20–30%.', 'Amonia meningkat akibat sisa pakan berlebih.');
        }
        // R07: Akumulasi limbah berlebih
        if ($cekFakta(['F27', 'F23', 'F16'])) {
            return $this->formatHasil('R07', 'D07', 'Akumulasi limbah berlebih', 'K07', 'Ganti air 30–40%; siphon dasar kolam; tambah probiotik; kurangi dosis pakan 25%.', 'PERINGATAN: Akumulasi limbah tinggi.');
        }
        // R08: Kolaps bioflok
        if ($cekFakta(['F24', 'F27'])) {
            return $this->formatHasil('R08', 'D08', 'Kolaps bioflok', 'K08', 'Hentikan pakan segera; ganti air 50%; aktifkan aerasi penuh; tambah probiotik dan molase.', 'DARURAT: Kolaps bioflok. Tindakan darurat diperlukan sekarang.');
        }
        // R09: Flok belum terbentuk
        if ($cekFakta(['F29', 'F25', 'F22', 'F33'])) {
            return $this->formatHasil('R09', 'D09', 'Flok belum terbentuk / populasi bakteri rendah', 'K09', 'Tambahkan molase atau sumber karbon lain; tambah probiotik starter.', 'Flok terlalu rendah. Tambahkan sumber karbon segera.');
        }
        // R10: Flok berlebih menguras oksigen
        if ($cekFakta(['F30', 'F23', 'F32'])) {
            return $this->formatHasil('R10', 'D10', 'Flok berlebih — menguras oksigen', 'K10', 'Siphon endapan flok dari dasar kolam; kurangi pemberian pakan; lakukan pergantian air 20-30%.', 'PERINGATAN: Flok berlebih menyebabkan DO turun.');
        }
        // R11: Flok kolaps akibat amonia
        if ($cekFakta(['F29', 'F27'])) {
            return $this->formatHasil('R11', 'D11', 'Flok kolaps akibat amonia tinggi', 'K11', 'Hentikan pakan segera; ganti air 40–50%; tambah probiotik dan molase untuk bangun ulang flok.', 'DARURAT: Sistem bioflok tidak berfungsi.');
        }
        // R12: Blooming fitoplankton
        if ($cekFakta(['F28', 'F32', 'F21'])) {
            return $this->formatHasil('R12', 'D12', 'Blooming fitoplankton berlebih', 'K12', 'Hentikan pemupukan, ganti air permukaan sebesar 20%, dan kurangi intensitas cahaya matahari.', 'PERINGATAN: Blooming fitoplankton berlebih.');
        }

        // DEFAULT: Jika tidak ada anomali
        return $this->formatHasil('R-DEFAULT', 'D-NORMAL', 'Kondisi Air Optimal (Normal)', 'K-NORMAL', 'Lanjutkan SOP pemeliharaan dan pemberian pakan seperti biasa. Tidak ada tindakan darurat.', 'Aman. Kualitas air terkendali.');
    }

    private function formatHasil($rule, $kodeD, $label, $kodeK, $mitigasi, $peringatan)
    {
        return [
            'rule_terpicu'      => [$rule],
            'kode_diagnosa'     => $kodeD,
            'label_diagnosa'    => $label,
            'kode_kesimpulan'   => $kodeK,
            'tindakan_mitigasi' => $mitigasi,
            'peringatan'        => $peringatan
        ];
    }
}