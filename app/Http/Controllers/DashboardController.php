<?php

namespace App\Http\Controllers;

use App\Models\DailyParameter;
use App\Models\HarvestLog;
use App\Models\Inventory;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\TebarLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // =========================================================================
        // 1. DATA RINGKASAN & RINCIAN KOLAM
        // =========================================================================
        $kolams = Kolam::all();
        $totalKolam = $kolams->count();
        $totalIkan = $kolams->sum('jumlah_ikan');

        $totalBiomassaKg = 0;
        $kolam_list = [];

        foreach ($kolams as $kolam) {
            // Rumus biomassa: (jumlah ikan * berat rata-rata gram) / 1000 (dikonversi ke Kg)
            $biomassa = ($kolam->jumlah_ikan * $kolam->berat_rata_gram) / 1000;
            $totalBiomassaKg += $biomassa;

            $kolam_list[] = [
                'id' => $kolam->id,
                'nama' => $kolam->nama_kolam,
                'populasi' => $kolam->jumlah_ikan,
            ];
        }

        // =========================================================================
        // 2. DATA INVENTORY (DENGAN MACHINE LEARNING - RANDOM FOREST)
        // =========================================================================
        $inventories = Inventory::all();
        $stokPakan = $inventories->sum('total_stok_kg');

        // --- A. Siapkan Kondisi Kolam Hari Ini ---
        $parameterTerbaru = DailyParameter::latest('tanggal_cek')->first();
        $avgBerat = DailyParameter::whereDate('tanggal_cek', Carbon::today())->avg('berat_sample') ?? 100;

        // Mapping Kondisi Visual ke Angka untuk Model ML
        $mapKejernihan = ['normal' => 1, 'agak keruh' => 2, 'keruh' => 3, 'pekat' => 3];
        $kejernihanAngka = $parameterTerbaru ? ($mapKejernihan[strtolower($parameterTerbaru->kondisi_visual)] ?? 1) : 1;

        $kondisi_sekarang = [
            'populasi' => (int) $totalIkan,
            'berat_rata_rata' => (float) $avgBerat,
            'suhu_air' => (float) ($parameterTerbaru ? $parameterTerbaru->suhu : 28.0),
            'ph_air' => (float) ($parameterTerbaru ? $parameterTerbaru->ph : 7.0),
            'kejernihan' => $kejernihanAngka,
        ];

        // --- B. Siapkan Array Riwayat Pakan 7 Hari Terakhir ---
        $riwayat_data = [];
        for ($i = 7; $i >= 1; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');

            $param = DailyParameter::whereDate('tanggal_cek', $date)->first();
            $pakanHabis = DB::table('feed_log_details')
                ->join('feed_logs', 'feed_log_details.feed_log_id', '=', 'feed_logs.id')
                ->whereDate('feed_logs.tanggal_pakan', $date)
                ->sum('feed_log_details.jumlah_kg');

            // Hanya kirim ke ML jika di hari tersebut ada data pemberian pakan
            if ($pakanHabis > 0) {
                $riwayat_data[] = [
                    'populasi' => (int) $totalIkan,
                    'berat_rata_rata' => (float) ($param ? $param->berat_sample : $avgBerat),
                    'suhu_air' => (float) ($param ? $param->suhu : 28.0),
                    'ph_air' => (float) ($param ? $param->ph : 7.0),
                    'kejernihan' => $param ? ($mapKejernihan[strtolower($param->kondisi_visual)] ?? 1) : 1,
                    'pakan_habis' => (float) $pakanHabis,
                ];
            }
        }

        // --- C. Loop Setiap Item di Gudang dan Tembak ke API ---
        $inventory_list = $inventories->map(function ($inv) use ($kondisi_sekarang, $riwayat_data) {
            $lajuHarian = 0;
            $estimasiHari = 0;
            $status = 'Belum Diprediksi';

            // Jika stok kosong, tidak perlu memanggil API ML
            if ($inv->total_stok_kg <= 0) {
                return [
                    'id' => $inv->id,
                    'nama' => $inv->nama_pakan,
                    'stok' => $inv->total_stok_kg,
                    'rata_rata' => 0,
                    'estimasi' => 0,
                    'status' => 'Habis',
                ];
            }

            try {
                // Endpoint ML API (Pastikan ML_API_URL sudah ada di .env)
                $apiUrl = env('ML_API_URL', 'http://127.0.0.1:8000').'/predict-stock';

                // Set timeout singkat (3 detik) agar dashboard tidak loading lama jika server Python mati
                $response = Http::timeout(3)->post($apiUrl, [
                    'riwayat_data' => $riwayat_data,
                    'kondisi_sekarang' => $kondisi_sekarang,
                    'stok_gudang' => (float) $inv->total_stok_kg,
                ]);

                if ($response->successful()) {
                    $mlResult = $response->json();
                    $lajuHarian = $mlResult['predicted_daily_consumption'];
                    $estimasiHari = $mlResult['estimated_days_left'];
                    $status = $mlResult['status'];
                } else {
                    // Terjadi jika jumlah array riwayat_data kurang dari batas minimal yang diminta Python (misal < 5)
                    $status = 'Kurang Data ML';
                }
            } catch (\Exception $e) {
                // Tangkap error jika API mati atau menolak koneksi
                Log::error('ML API Connection Error: '.$e->getMessage());
                $status = 'Server AI Offline';
            }

            return [
                'id' => $inv->id,
                'nama' => $inv->nama_pakan,
                'stok' => $inv->total_stok_kg,
                'rata_rata' => round($lajuHarian, 2),
                'estimasi' => $estimasiHari,
                'status' => $status,
            ];
        });

        // =========================================================================
        // SETUP DATA GRAFIK (7 HARI TERAKHIR)
        // =========================================================================
        $labels7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels7Hari[] = Carbon::today()->subDays($i)->translatedFormat('d M');
        }

        // -------------------------------------------------------------------------
        // 3. CHART PAKAN (Rincian Konsumsi Per Jenis Pakan)
        // -------------------------------------------------------------------------
        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');

        $rawPakan = DB::table('feed_log_details')
            ->join('feed_logs', 'feed_log_details.feed_log_id', '=', 'feed_logs.id')
            ->whereDate('feed_logs.tanggal_pakan', '>=', $startDate)
            ->select(
                'feed_log_details.inventory_id',
                DB::raw('DATE(feed_logs.tanggal_pakan) as tanggal'),
                DB::raw('SUM(feed_log_details.jumlah_kg) as total_kg')
            )
            ->groupBy('feed_log_details.inventory_id', 'tanggal')
            ->get();

        $colorPalette = [
            ['border' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'], // Biru
            ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'], // Hijau
            ['border' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)'], // Ungu
            ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'], // Amber
            ['border' => '#ec4899', 'bg' => 'rgba(236, 72, 153, 0.1)'], // Pink
        ];

        $pakanDatasets = [];
        $colorIndex = 0;

        foreach ($inventories as $inv) {
            $dataPemakaian = [];
            $adaPemakaian = false;

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');

                $match = $rawPakan->first(function ($item) use ($inv, $date) {
                    return $item->inventory_id == $inv->id && $item->tanggal == $date;
                });

                $jumlah = $match ? $match->total_kg : 0;
                $dataPemakaian[] = $jumlah ? round($jumlah, 2) : 0;

                if ($jumlah > 0) {
                    $adaPemakaian = true;
                }
            }

            if ($adaPemakaian) {
                $theme = $colorPalette[$colorIndex % count($colorPalette)];
                $pakanDatasets[] = [
                    'label' => $inv->nama_pakan,
                    'data' => $dataPemakaian,
                    'borderColor' => $theme['border'],
                    'backgroundColor' => $theme['bg'],
                    'fill' => true,
                    'tension' => 0.4,
                ];
                $colorIndex++;
            }
        }

        if (empty($pakanDatasets)) {
            $pakanDatasets[] = [
                'label' => 'Belum ada data',
                'data' => array_fill(0, 7, 0),
                'borderColor' => '#cbd5e1',
                'backgroundColor' => 'rgba(203, 213, 225, 0.1)',
                'fill' => true,
                'tension' => 0.4,
            ];
        }

        $chartPakan = [
            'labels' => $labels7Hari,
            'datasets' => $pakanDatasets,
        ];

        // -------------------------------------------------------------------------
        // 4. CHART BERAT (Kurva Pertumbuhan Ikan Progresif)
        // -------------------------------------------------------------------------
        $dataBeratAktual = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $avgBerat = DailyParameter::whereDate('tanggal_cek', $date)->avg('berat_sample');
            $dataBeratAktual[] = $avgBerat ? round($avgBerat, 2) : 0;
        }

        $chartBerat = [
            'labels' => $labels7Hari,
            'datasets' => [
                [
                    'label' => 'Berat Rata-rata',
                    'data' => $dataBeratAktual,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];

        // -------------------------------------------------------------------------
        // 5. CHART POPULASI (Reverse Calculation untuk mencari populasi harian)
        // -------------------------------------------------------------------------
        $historicalPopulasi = [];
        $runningTotal = $totalIkan;

        for ($i = 0; $i <= 6; $i++) {
            if ($i == 0) {
                $historicalPopulasi[] = $runningTotal;
            } else {
                $besoknya = Carbon::today()->subDays($i - 1)->format('Y-m-d');

                $tebar = TebarLog::whereDate('tanggal_tebar', $besoknya)->sum('jumlah_ikan');
                $mati = MortalityLog::whereDate('tanggal_kematian', $besoknya)->sum('jumlah_mati');
                $panen = HarvestLog::whereDate('tanggal_panen', $besoknya)->sum('jumlah_ikan');

                $runningTotal = $runningTotal - $tebar + $mati + $panen;
                $historicalPopulasi[] = $runningTotal;
            }
        }

        $dataPopulasi = array_reverse($historicalPopulasi);

        $chartPopulasi = [
            'labels' => $labels7Hari,
            'datasets' => [
                [
                    'label' => 'Total Populasi',
                    'data' => $dataPopulasi,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
        ];

        // =========================================================================
        // RENDER KE VUE (INERTIA)
        // =========================================================================
        return Inertia::render('Dashboard', [
            'ringkasan' => [
                'totalKolam' => $totalKolam,
                'totalIkan' => $totalIkan,
                'totalBiomassaKg' => round($totalBiomassaKg, 2),
            ],
            'kolam_list' => $kolam_list,
            'inventory' => [
                'stokPakan' => round($stokPakan, 2),
            ],
            'inventory_list' => $inventory_list,
            'chartPakan' => $chartPakan,
            'chartBerat' => $chartBerat,
            'chartPopulasi' => $chartPopulasi,
        ]);
    }
}
