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
        ];

        $faktaBaru = $this->evaluasiFakta($faktaInput);

        $hasilDiagnosa = $this->cocokkanRule($faktaBaru);

        $isNormal = in_array('DN', $hasilDiagnosa['kode_diagnosa']);

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
            $labelText = mb_substr(implode('; ', $hasilDiagnosa['label_diagnosa']), 0, 480);
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

        if ($input['suhu'] < 27.0) {
            $fakta[] = 'F01';
        } elseif ($input['suhu'] <= 32.0) {
            $fakta[] = 'F02';
        } else {
            $fakta[] = 'F03';
        }

        if ($input['ph'] < 5.5) {
            $fakta[] = 'F04';
        } elseif ($input['ph'] <= 8.5) {
            $fakta[] = 'F05';
        } else {
            $fakta[] = 'F06';
        }

        if ($input['do'] < 5.0) {
            $fakta[] = 'F07';
        } else {
            $fakta[] = 'F08';
        }

        if ($input['amonia'] < 0.01) {
            $fakta[] = 'F09';
        } else {
            $fakta[] = 'F10';
        }

        if ($input['flok'] < 15.0) {
            $fakta[] = 'F11';
        } elseif ($input['flok'] <= 40.0) {
            $fakta[] = 'F12';
        } else {
            $fakta[] = 'F13';
        }

        return $fakta;
    }

    private function cocokkanRule(array $fakta): array
    {
        $cekFakta = function ($syarat) use ($fakta) {
            foreach ($syarat as $s) {
                if (is_array($s)) {
                    if (count(array_intersect($s, $fakta)) === 0) {
                        return false;
                    }
                } elseif (! in_array($s, $fakta)) {
                    return false;
                }
            }

            return true;
        };

        $tindakanMap = [
            'K01' => 'Stabilkan suhu ke rentang normal (tutup kolam/heater jika dingin; naungan/naikkan air jika panas)',
            'K02' => 'Stabilkan pH ke rentang normal (tambah kapur dolomit jika asam; kurangi kapur/ganti air jika basa)',
            'K03' => 'Tambah aerasi (nyalakan/tambah kincir, periksa sistem aerasi)',
            'K04' => 'Lakukan pergantian air parsial (20–30%)',
            'K05' => 'Hentikan atau kurangi pemberian pakan sementara',
            'K06' => 'Sesuaikan dosis sumber karbon/molase (tambah jika flok kurang, kurangi jika flok/kekeruhan berlebih)',
            'K07' => 'Siphon/buang endapan flok dan sisa pakan dari dasar kolam',
            'K08' => 'Periksa kondisi fisik ikan (luka, bercak putih, lendir berlebih, perilaku tidak normal)',
            'K09' => 'Pisahkan ikan yang menunjukkan gejala sakit ke kolam karantina',
            'K10' => 'Kurangi kepadatan tebar jika kondisi kritis berulang',
        ];

        $ruleCodes = [];
        $kodeD = [];
        $labelD = [];
        $kodeK = [];
        $tindakan = [];
        $peringatan = [];
        $seenK = [];

        $rules = [
            'D01' => [
                'fakta' => ['F01'],
                'label' => 'Gangguan Metabolisme & Imunitas Menurun (Stres Suhu Rendah)',
                'tindakan_kode' => ['K01'],
                'peringatan' => 'Suhu di bawah rentang optimal. Imunitas ikan menurun, pantau kembali dalam 2 jam.',
            ],
            'D02' => [
                'fakta' => ['F03'],
                'label' => 'Stres Panas & Peningkatan Kebutuhan Oksigen (Stres Suhu Tinggi)',
                'tindakan_kode' => ['K01', 'K03'],
                'peringatan' => 'Suhu di atas rentang optimal. Kebutuhan oksigen ikan meningkat, pantau kembali dalam 2 jam.',
            ],
            'D03' => [
                'fakta' => ['F04'],
                'label' => 'Iritasi Kulit & Produksi Lendir Berlebih (Stres Air Asam)',
                'tindakan_kode' => ['K02', 'K08'],
                'peringatan' => 'pH terlalu asam. Berisiko mengiritasi kulit ikan, lakukan koreksi bertahap dan pantau ulang dalam 3 jam.',
            ],
            'D04' => [
                'fakta' => ['F06'],
                'label' => 'Kerusakan Jaringan Insang (Stres Air Basa)',
                'tindakan_kode' => ['K02', 'K04'],
                'peringatan' => 'pH terlalu basa. Berisiko merusak jaringan insang, lakukan koreksi bertahap dan pantau ulang dalam 3 jam.',
            ],
            'D05' => [
                'fakta' => ['F07'],
                'label' => 'Hipoksia — Sesak Napas Akut',
                'tindakan_kode' => ['K03', 'K05'],
                'peringatan' => 'Oksigen rendah, ikan berisiko sesak napas akut. Tindakan segera diperlukan.',
            ],
            'D06' => [
                'fakta' => ['F10', 'F06'],
                'label' => 'Keracunan Amonia Akut (Toksisitas NH3 Diperparah pH Basa)',
                'tindakan_kode' => ['K04', 'K05', 'K03'],
                'peringatan' => 'Risiko keracunan amonia akut. Toksisitas NH3 meningkat pada pH basa, tindakan segera diperlukan.',
            ],
            'D07' => [
                'fakta' => ['F10', ['F05', 'F04']],
                'label' => 'Keracunan Amonia Kronis',
                'tindakan_kode' => ['K04', 'K05'],
                'peringatan' => 'Risiko keracunan amonia kronis jika berlangsung lama. Cegah akumulasi lebih lanjut.',
            ],
            'D08' => [
                'fakta' => ['F11'],
                'label' => 'Pertumbuhan Terhambat Akibat Sistem Bioflok Belum Matang',
                'tindakan_kode' => ['K06'],
                'peringatan' => 'Kepadatan flok di bawah optimal. Berisiko menghambat pertumbuhan ikan jangka panjang.',
            ],
            'D09' => [
                'fakta' => ['F13'],
                'label' => 'Penurunan Oksigen Akibat Respirasi Bakteri Flok Berlebih',
                'tindakan_kode' => ['K06', 'K07', 'K03'],
                'peringatan' => 'Kepadatan flok berlebih. Respirasi bakteri flok berisiko menurunkan oksigen terlarut, terutama malam hari.',
            ],
            'D10' => [
                'fakta' => ['F07', 'F10'],
                'label' => 'Motile Aeromonas Septicemia (MAS) — Infeksi Bakteri Aeromonas',
                'tindakan_kode' => ['K04', 'K03', 'K05', 'K08'],
                'peringatan' => 'Risiko infeksi bakteri Aeromonas (MAS). Kombinasi DO rendah dan amonia tinggi melemahkan imunitas ikan.',
            ],
            'D11' => [
                'fakta' => ['F03', 'F07'],
                'label' => 'Stres Metabolik Akut & Risiko Kematian Mendadak',
                'tindakan_kode' => ['K01', 'K03', 'K10'],
                'peringatan' => 'Risiko kematian mendadak. Suhu tinggi meningkatkan kebutuhan oksigen saat pasokan justru menurun.',
            ],
            'D12' => [
                'fakta' => ['F01'],
                'label' => 'Saprolegniasis — Infeksi Jamur pada Kulit/Sirip',
                'tindakan_kode' => ['K01', 'K08', 'K09'],
                'peringatan' => 'Risiko infeksi jamur (Saprolegniasis). Suhu rendah menekan imun ikan.',
            ],
            'D13' => [
                'fakta' => ['F10', 'F03'],
                'label' => 'Streptococcosis — Infeksi Bakteri Streptococcus (Ikan Imun Rendah)',
                'tindakan_kode' => ['K01', 'K04', 'K08'],
                'peringatan' => 'Risiko infeksi bakteri Streptococcus. Amonia tinggi dan suhu tinggi bersamaan melemahkan imunitas ikan secara signifikan.',
            ],
        ];

        foreach ($rules as $kode => $rule) {
            if ($cekFakta($rule['fakta'])) {
                $ruleCodes[] = $kode;
                $kodeD[] = $kode;
                $labelD[] = $rule['label'];
                $peringatan[] = $rule['peringatan'];

                foreach ($rule['tindakan_kode'] as $k) {
                    if (! in_array($k, $seenK)) {
                        $seenK[] = $k;
                        $kodeK[] = $k;
                        $tindakan[] = $tindakanMap[$k];
                    }
                }
            }
        }

        if (count($kodeD) === 0) {
            return [
                'rule_terpicu' => ['DN'],
                'kode_diagnosa' => ['DN'],
                'label_diagnosa' => ['Kondisi Kolam Optimal — Risiko Penyakit Rendah'],
                'kode_kesimpulan' => ['KN'],
                'tindakan_mitigasi' => ['Tidak ada tindakan; lanjutkan monitoring rutin sesuai jadwal'],
                'peringatan' => ['Kondisi kolam optimal, risiko penyakit rendah.'],
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
