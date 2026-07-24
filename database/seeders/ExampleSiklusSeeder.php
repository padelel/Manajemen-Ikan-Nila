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
            if (! in_array('DN', $fc['kode_diagnosa'])) {
                $jam = rand(8, 16);
                $deadline = (clone $tanggal)->setTime($jam, rand(0, 59));
                $labelText = mb_substr(implode('; ', $fc['label_diagnosa']), 0, 480);
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
        return [
            1 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 22.0],
            2 => ['suhu' => 28.5, 'ph' => 7.1, 'do' => 5.8, 'amonia' => 0.003, 'flok' => 24.0],
            3 => ['suhu' => 27.5, 'ph' => 7.3, 'do' => 6.2, 'amonia' => 0.004, 'flok' => 20.0],
            4 => ['suhu' => 29.0, 'ph' => 7.0, 'do' => 5.5, 'amonia' => 0.002, 'flok' => 25.0],
            5 => ['suhu' => 28.0, 'ph' => 5.0, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 22.0], // F04 → D03 pH asam
            6 => ['suhu' => 28.0, 'ph' => 8.8, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 22.0], // F06 → D04 pH basa
            7 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 22.0],
            8 => ['suhu' => 27.0, 'ph' => 7.4, 'do' => 5.5, 'amonia' => 0.004, 'flok' => 23.0],
            9 => ['suhu' => 28.5, 'ph' => 7.1, 'do' => 6.0, 'amonia' => 0.003, 'flok' => 21.0],
            10 => ['suhu' => 29.0, 'ph' => 7.3, 'do' => 5.8, 'amonia' => 0.005, 'flok' => 24.0],
            11 => ['suhu' => 27.5, 'ph' => 7.2, 'do' => 5.5, 'amonia' => 0.002, 'flok' => 22.0],
            12 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 4.2, 'amonia' => 0.005, 'flok' => 22.0], // F07 → D05 hipoksia
            13 => ['suhu' => 28.0, 'ph' => 7.0, 'do' => 5.5, 'amonia' => 0.005, 'flok' => 25.0],
            14 => ['suhu' => 28.5, 'ph' => 7.1, 'do' => 6.0, 'amonia' => 0.003, 'flok' => 23.0],
            15 => ['suhu' => 27.0, 'ph' => 7.3, 'do' => 5.8, 'amonia' => 0.004, 'flok' => 22.0],
            16 => ['suhu' => 29.0, 'ph' => 7.2, 'do' => 5.5, 'amonia' => 0.005, 'flok' => 24.0],
            17 => ['suhu' => 28.0, 'ph' => 7.4, 'do' => 6.0, 'amonia' => 0.002, 'flok' => 26.0],
            18 => ['suhu' => 28.0, 'ph' => 7.0, 'do' => 3.5, 'amonia' => 0.08, 'flok' => 22.0], // F07+F10+F05 → D05+D07+D10
            19 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 5.5, 'amonia' => 0.005, 'flok' => 24.0],
            20 => ['suhu' => 28.5, 'ph' => 7.1, 'do' => 6.0, 'amonia' => 0.004, 'flok' => 23.0],
            21 => ['suhu' => 27.5, 'ph' => 7.3, 'do' => 5.8, 'amonia' => 0.003, 'flok' => 22.0],
            22 => ['suhu' => 28.0, 'ph' => 7.0, 'do' => 2.5, 'amonia' => 0.12, 'flok' => 25.0], // F07+F10 → D05+D10
            23 => ['suhu' => 29.0, 'ph' => 7.2, 'do' => 5.5, 'amonia' => 0.005, 'flok' => 24.0],
            24 => ['suhu' => 28.0, 'ph' => 7.0, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 10.0], // F11 → D08 flok rendah
            25 => ['suhu' => 33.0, 'ph' => 7.0, 'do' => 6.0, 'amonia' => 0.02, 'flok' => 22.0], // F03+F10 → D02+D13
            26 => ['suhu' => 26.0, 'ph' => 7.2, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 22.0], // F01 → D01+D12 suhu rendah
            27 => ['suhu' => 28.0, 'ph' => 8.8, 'do' => 6.0, 'amonia' => 0.02, 'flok' => 24.0], // F06+F10 → D04+D06
            28 => ['suhu' => 33.0, 'ph' => 7.0, 'do' => 4.0, 'amonia' => 0.005, 'flok' => 22.0], // F03+F07 → D02+D11
            29 => ['suhu' => 28.0, 'ph' => 7.2, 'do' => 6.0, 'amonia' => 0.005, 'flok' => 45.0], // F13 → D09 flok tinggi
            30 => ['suhu' => 28.5, 'ph' => 7.1, 'do' => 5.8, 'amonia' => 0.004, 'flok' => 23.0],
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
