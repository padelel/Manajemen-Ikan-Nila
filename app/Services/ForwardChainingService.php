<?php

namespace App\Services;

use App\Models\InferensiLog;
use App\Models\ParameterHarian;
use App\Models\Tiket;
use Carbon\Carbon;

class ForwardChainingService
{
    public function prosesInferensi(ParameterHarian $parameter)
    {
        $faktaInput = [
            'suhu' => (float) $parameter->suhu,
            'ph' => (float) $parameter->ph,
            'do' => (float) $parameter->do_mgl,
            'amonia' => (float) $parameter->amonia_mgl,
            'flok' => (float) $parameter->flok_ml,
            'kecerahan' => (float) $parameter->kecerahan_cm,
        ];

        $faktaBaru = $this->evaluasiFakta($faktaInput);

        $hasilDiagnosa = $this->cocokkanRule($faktaBaru);

        $isNormal = in_array('D-NORMAL', $hasilDiagnosa['kode_diagnosa']);

        $log = InferensiLog::create([
            'kolam_id' => $parameter->kolam_id,
            'parameter_harian_id' => $parameter->id,
            'tanggal_inferensi' => Carbon::now()->toDateString(),
            'fakta_input' => $faktaInput,
            'fakta_baru' => $faktaBaru,
            'rule_terpicu' => $hasilDiagnosa['rule_terpicu'],
            'kode_diagnosa' => $hasilDiagnosa['kode_diagnosa'],
            'label_diagnosa' => $hasilDiagnosa['label_diagnosa'],
            'kode_kesimpulan' => $hasilDiagnosa['kode_kesimpulan'],
            'tindakan_mitigasi' => $hasilDiagnosa['tindakan_mitigasi'],
            'peringatan' => $hasilDiagnosa['peringatan'],
        ]);

        if (! $isNormal) {
            $labelText = implode('; ', $hasilDiagnosa['label_diagnosa']);
            $tindakanText = implode("\n", $hasilDiagnosa['tindakan_mitigasi']);

            Tiket::create([
                'kolam_id' => $parameter->kolam_id,
                'inferensi_log_id' => $log->id,
                'operator_id' => $parameter->user_id,
                'judul' => 'Mitigasi Darurat: '.$labelText,
                'deskripsi_tindakan' => $tindakanText,
                'status' => 'open',
                'deadline' => Carbon::now()->addHours(3),
            ]);
        }

        return $log;
    }

    private function evaluasiFakta(array $input): array
    {
        $fakta = [];

        if ($input['suhu'] >= 25.0 && $input['suhu'] <= 30.0) {
            $fakta[] = 'F19';
        } elseif (($input['suhu'] >= 20.0 && $input['suhu'] <= 24.99) || ($input['suhu'] >= 30.01 && $input['suhu'] <= 34.0)) {
            $fakta[] = 'F20';
        } elseif ($input['suhu'] < 20.0 || $input['suhu'] > 34.0) {
            $fakta[] = 'F21';
        }

        if ($input['ph'] >= 6.5 && $input['ph'] <= 8.5) {
            $fakta[] = 'F22';
        } elseif (($input['ph'] >= 6.0 && $input['ph'] <= 6.49) || ($input['ph'] >= 8.51 && $input['ph'] <= 9.0)) {
            $fakta[] = 'F23';
        } elseif ($input['ph'] < 6.0 || $input['ph'] > 9.0) {
            $fakta[] = 'F24';
        }

        if ($input['do'] >= 5.0) {
            $fakta[] = 'F25';
        } elseif ($input['do'] >= 3.0 && $input['do'] <= 4.99) {
            $fakta[] = 'F26';
        } elseif ($input['do'] < 3.0) {
            $fakta[] = 'F27';
        }

        if ($input['amonia'] < 0.01) {
            $fakta[] = 'F28';
        } elseif ($input['amonia'] >= 0.01 && $input['amonia'] <= 0.05) {
            $fakta[] = 'F29';
        } elseif ($input['amonia'] > 0.05) {
            $fakta[] = 'F30';
        }

        if ($input['flok'] >= 15.0 && $input['flok'] <= 30.0) {
            $fakta[] = 'F31';
        } elseif ($input['flok'] < 15.0) {
            $fakta[] = 'F32';
        } elseif ($input['flok'] > 30.0) {
            $fakta[] = 'F33';
        }

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

        $diagnosa = [];
        $ruleCodes = [];
        $kodeD = [];
        $labelD = [];
        $kodeK = [];
        $tindakan = [];
        $peringatan = [];

        if ($cekFakta(['F21'])) {
            $diagnosa[] = 'D02';
            $ruleCodes[] = 'D02';
            $kodeD[] = 'D02';
            $labelD[] = 'Stres Suhu Kritis';
            $kodeK[] = 'K02';
            $tindakan[] = 'Hentikan pakan sementara; ganti air parsial 20–30% dengan air bersuhu sesuai';
            $peringatan[] = 'PERINGATAN: Suhu kritis. Risiko kematian ikan jika tidak segera ditangani.';
        }

        if ($cekFakta(['F35', 'F24', 'F31'])) {
            $diagnosa[] = 'D12';
            $ruleCodes[] = 'D12';
            $kodeD[] = 'D12';
            $labelD[] = 'Blooming Fitoplankton Berlebih';
            $kodeK[] = 'K12';
            $tindakan[] = 'Hentikan pemupukan; ganti air permukaan 20%; kurangi intensitas cahaya matahari langsung dengan paranet';
            $peringatan[] = 'PERINGATAN: Blooming fitoplankton berlebih. Kecerahan air menurun drastis.';
        }

        if ($cekFakta(['F24'])) {
            $diagnosa[] = 'D04';
            $ruleCodes[] = 'D04';
            $kodeD[] = 'D04';
            $labelD[] = 'pH Ekstrem Kritis';
            $kodeK[] = 'K04';
            $tindakan[] = 'Hentikan pakan; ganti air masif 40–50%; tambahkan buffer pH';
            $peringatan[] = 'DARURAT: pH kritis. Hentikan pakan dan lakukan koreksi air sekarang.';
        }

        if ($cekFakta(['F27', 'F30'])) {
            $diagnosa[] = 'D08';
            $ruleCodes[] = 'D08';
            $kodeD[] = 'D08';
            $labelD[] = 'Kolaps Bioflok';
            $kodeK[] = 'K08';
            $tindakan[] = 'Hentikan pakan segera; ganti air 50%; aktifkan aerasi penuh; tambah probiotik dan molase';
            $peringatan[] = 'DARURAT: Kolaps bioflok. DO kritis dan amonia sangat tinggi. Tindakan darurat diperlukan.';
        }

        if ($cekFakta(['F32', 'F30'])) {
            $diagnosa[] = 'D11';
            $ruleCodes[] = 'D11';
            $kodeD[] = 'D11';
            $labelD[] = 'Flok Kolaps Akibat Amonia Tinggi';
            $kodeK[] = 'K11';
            $tindakan[] = 'Hentikan pakan segera; ganti air 40–50%; tambah probiotik dan molase untuk bangun ulang flok';
            $peringatan[] = 'DARURAT: Flok kolaps akibat amonia tinggi. Sistem bioflok tidak berfungsi.';
        }

        if ($cekFakta(['F30', 'F26', 'F19', 'F35'])) {
            $diagnosa[] = 'D07';
            $ruleCodes[] = 'D07';
            $kodeD[] = 'D07';
            $labelD[] = 'Akumulasi Limbah Berlebih';
            $kodeK[] = 'K07';
            $tindakan[] = 'Ganti air 30–40%; siphon dasar kolam; tambah probiotik; kurangi dosis pakan 25%';
            $peringatan[] = 'PERINGATAN: Akumulasi limbah tinggi. Lakukan pergantian air dan pembersihan segera.';
        }

        if ($cekFakta(['F33', 'F26', 'F35'])) {
            $diagnosa[] = 'D10';
            $ruleCodes[] = 'D10';
            $kodeD[] = 'D10';
            $labelD[] = 'Flok Berlebih — Menguras Oksigen';
            $kodeK[] = 'K10';
            $tindakan[] = 'Siphon endapan flok dari dasar kolam; kurangi pakan; ganti air parsial 20–30%';
            $peringatan[] = 'PERINGATAN: Flok berlebih menyebabkan DO turun. Lakukan pengelolaan flok segera.';
        }

        if ($cekFakta(['F20', 'F22', 'F25', 'F28', 'F34'])) {
            $diagnosa[] = 'D01';
            $ruleCodes[] = 'D01';
            $kodeD[] = 'D01';
            $labelD[] = 'Stres Suhu Ringan';
            $kodeK[] = 'K01';
            $tindakan[] = 'Tutupi kolam dengan paranet jika suhu panas; tambah aerasi untuk pendinginan';
            $peringatan[] = 'Suhu tidak ideal. Pantau setiap 2 jam.';
        }

        if ($cekFakta(['F23', 'F19', 'F25', 'F28', 'F34'])) {
            $diagnosa[] = 'D03';
            $ruleCodes[] = 'D03';
            $kodeD[] = 'D03';
            $labelD[] = 'Gangguan pH';
            $kodeK[] = 'K03';
            $tindakan[] = 'Tambahkan kapur (pH rendah) atau ganti air parsial 10–20% (pH tinggi)';
            $peringatan[] = 'pH tidak ideal. Lakukan koreksi dan pantau ulang dalam 3 jam.';
        }

        if ($cekFakta(['F26', 'F19', 'F28', 'F31', 'F34'])) {
            $diagnosa[] = 'D05';
            $ruleCodes[] = 'D05';
            $kodeD[] = 'D05';
            $labelD[] = 'Aerasi Kurang';
            $kodeK[] = 'K05';
            $tindakan[] = 'Periksa dan perbaiki pompa udara/kincir; tambah titik aerasi';
            $peringatan[] = 'DO rendah karena aerasi kurang. Periksa sistem aerasi segera.';
        }

        if ($cekFakta(['F29', 'F19', 'F25', 'F31', 'F34'])) {
            $diagnosa[] = 'D06';
            $ruleCodes[] = 'D06';
            $kodeD[] = 'D06';
            $labelD[] = 'Overfeeding';
            $kodeK[] = 'K06';
            $tindakan[] = 'Hentikan pakan 1–2 hari; siphon sisa pakan di dasar kolam; ganti air 20–30%';
            $peringatan[] = 'Amonia meningkat akibat sisa pakan berlebih. Hentikan pakan dan bersihkan kolam.';
        }

        if ($cekFakta(['F32', 'F28', 'F25', 'F36'])) {
            $diagnosa[] = 'D09';
            $ruleCodes[] = 'D09';
            $kodeD[] = 'D09';
            $labelD[] = 'Flok Belum Terbentuk / Bakteri Rendah';
            $kodeK[] = 'K09';
            $tindakan[] = 'Tambahkan molase/sumber karbon untuk memacu bakteri; tambah probiotik starter bioflok';
            $peringatan[] = 'Flok terlalu rendah. Sistem bioflok belum optimal. Tambahkan sumber karbon segera.';
        }

        if (count($diagnosa) === 0) {
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
