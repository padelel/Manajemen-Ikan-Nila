<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\FeedLog;
use App\Models\MortalityLog;
use App\Models\HarvestLog;
use App\Models\TebarLog;
use App\Models\DailyParameter;

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
                'populasi' => $kolam->jumlah_ikan
            ];
        }

        // =========================================================================
        // 2. DATA INVENTORY (SISA STOK GUDANG)
        // =========================================================================
        $inventories = Inventory::all();
        $stokPakan = $inventories->sum('total_stok_kg');
        
        $inventory_list = $inventories->map(function($inv) {
            // Simulasi Laju Pakan Harian (Bisa diganti dengan query riil jika diperlukan)
            $lajuHarian = 5; 
            $estimasiHari = $lajuHarian > 0 ? floor($inv->total_stok_kg / $lajuHarian) : 0;
            
            $status = 'Aman';
            if ($inv->total_stok_kg == 0) {
                $status = 'Habis';
            } elseif ($estimasiHari <= 3) {
                $status = 'Kritis';
            } elseif ($estimasiHari <= 7) {
                $status = 'Waspada';
            }

            return [
                'id' => $inv->id,
                'nama' => $inv->nama_pakan,
                'stok' => $inv->total_stok_kg,
                'rata_rata' => $lajuHarian,
                'estimasi' => $estimasiHari,
                'status' => $status
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
        
        // Tarik data detail pakan dan gabungkan dengan feed_logs
        $rawPakan = \Illuminate\Support\Facades\DB::table('feed_log_details')
            ->join('feed_logs', 'feed_log_details.feed_log_id', '=', 'feed_logs.id')
            ->whereDate('feed_logs.tanggal_pakan', '>=', $startDate)
            ->select(
                'feed_log_details.inventory_id',
                \Illuminate\Support\Facades\DB::raw('DATE(feed_logs.tanggal_pakan) as tanggal'),
                \Illuminate\Support\Facades\DB::raw('SUM(feed_log_details.jumlah_kg) as total_kg')
            )
            ->groupBy('feed_log_details.inventory_id', 'tanggal')
            ->get();

        // Palet warna agar setiap garis pakan berbeda warna
        $colorPalette = [
            ['border' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.1)'], // Biru
            ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'], // Hijau
            ['border' => '#8b5cf6', 'bg' => 'rgba(139, 92, 246, 0.1)'], // Ungu
            ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'], // Amber
            ['border' => '#ec4899', 'bg' => 'rgba(236, 72, 153, 0.1)'], // Pink
        ];

        $pakanDatasets = [];
        $colorIndex = 0;

        // Looping semua stok pakan yang ada di gudang
        foreach ($inventories as $inv) {
            $dataPemakaian = [];
            $adaPemakaian = false;

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                
                // Cari data di $rawPakan yang sesuai dengan id pakan dan tanggal ini
                $match = $rawPakan->first(function ($item) use ($inv, $date) {
                    return $item->inventory_id == $inv->id && $item->tanggal == $date;
                });

                $jumlah = $match ? $match->total_kg : 0;
                $dataPemakaian[] = $jumlah ? round($jumlah, 2) : 0;
                
                if ($jumlah > 0) {
                    $adaPemakaian = true;
                }
            }

            // HANYA buatkan garis di chart jika pakan ini terpakai dalam 7 hari terakhir
            if ($adaPemakaian) {
                $theme = $colorPalette[$colorIndex % count($colorPalette)];
                $pakanDatasets[] = [
                    'label' => $inv->nama_pakan,
                    'data' => $dataPemakaian,
                    'borderColor' => $theme['border'],
                    'backgroundColor' => $theme['bg'],
                    'fill' => true,
                    'tension' => 0.4
                ];
                $colorIndex++;
            }
        }

        // Fallback: Jika tidak ada pakan yang digunakan sama sekali
        if (empty($pakanDatasets)) {
            $pakanDatasets[] = [
                'label' => 'Belum ada data',
                'data' => array_fill(0, 7, 0),
                'borderColor' => '#cbd5e1',
                'backgroundColor' => 'rgba(203, 213, 225, 0.1)',
                'fill' => true,
                'tension' => 0.4
            ];
        }

        $chartPakan = [
            'labels' => $labels7Hari,
            'datasets' => $pakanDatasets
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
                    'borderColor' => '#4f46e5', // indigo-600
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'fill' => true,
                    'tension' => 0.4 // Melengkung halus
                ]
            ]
        ];

        // -------------------------------------------------------------------------
        // 5. CHART POPULASI (Reverse Calculation untuk mencari populasi harian)
        // -------------------------------------------------------------------------
        $historicalPopulasi = [];
        $runningTotal = $totalIkan; // Titik mulai dari total populasi HARI INI
        
        for ($i = 0; $i <= 6; $i++) {
            if ($i == 0) {
                // Hari ini (Indeks 0)
                $historicalPopulasi[] = $runningTotal; 
            } else {
                // Hitung mundur populasi kemarin
                // Rumus = Populasi Sekarang - Ikan yang Ditebar Kemarin + Ikan Mati Kemarin + Ikan Panen Kemarin
                $besoknya = Carbon::today()->subDays($i - 1)->format('Y-m-d');
                
                $tebar = TebarLog::whereDate('tanggal_tebar', $besoknya)->sum('jumlah_ikan');
                $mati = MortalityLog::whereDate('tanggal_kematian', $besoknya)->sum('jumlah_mati'); // Kolom sudah diperbaiki
                $panen = HarvestLog::whereDate('tanggal_panen', $besoknya)->sum('jumlah_ikan');
                
                $runningTotal = $runningTotal - $tebar + $mati + $panen;
                $historicalPopulasi[] = $runningTotal;
            }
        }
        
        // Membalik urutan array agar grafik bergerak dari masa lalu (kiri) ke hari ini (kanan)
        $dataPopulasi = array_reverse($historicalPopulasi);

        $chartPopulasi = [
            'labels' => $labels7Hari,
            'datasets' => [
                [
                    'label' => 'Total Populasi',
                    'data' => $dataPopulasi,
                    'borderColor' => '#f59e0b', // amber-500
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ]
            ]
        ];

        // =========================================================================
        // RENDER KE VUE (INERTIA)
        // =========================================================================
        return Inertia::render('Dashboard', [
            'ringkasan' => [
                'totalKolam' => $totalKolam,
                'totalIkan' => $totalIkan,
                'totalBiomassaKg' => round($totalBiomassaKg, 2)
            ],
            'kolam_list' => $kolam_list,
            'inventory' => [
                'stokPakan' => round($stokPakan, 2)
            ],
            'inventory_list' => $inventory_list,
            'chartPakan' => $chartPakan,
            'chartBerat' => $chartBerat,
            'chartPopulasi' => $chartPopulasi
        ]);
    }
}