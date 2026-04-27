<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\FeedLog;
use App\Models\FeedLogDetail;
use App\Models\DailyParameter;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. RINGKASAN DATA ATAS
        $kolams = Kolam::with('siklusAktif')->get();
        $totalKolam = $kolams->count();
        $totalIkan = $kolams->sum('jumlah_ikan');
        $totalBiomassaKg = $kolams->sum(function ($kolam) {
            return ($kolam->jumlah_ikan * $kolam->berat_rata_gram) / 1000;
        });

        // Siapkan data detail tiap kolam untuk popover (hover)
        $kolamList = $kolams->map(function($kolam) {
            return [
                'id' => $kolam->id,
                'nama' => $kolam->nama_kolam,
                'populasi' => $kolam->jumlah_ikan
            ];
        });

        // 2. DATA INVENTORI GLOBAL
        $stokPakanGlobal = Inventory::sum('total_stok_kg');
        $namaPakanGlobal = 'Semua Pakan'; 

        $periodeHari = 7;
        $tanggalMulai = Carbon::today()->subDays($periodeHari - 1); 

        // 3. DATA GRAFIK KONSUMSI PAKAN (Multi-Line)
        $inventories = Inventory::all();
        
        $chartLabelsPakanRaw = [];
        $chartLabelsPakanDisplay = [];

        for ($i = $periodeHari - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabelsPakanRaw[] = $date->format('Y-m-d');
            $chartLabelsPakanDisplay[] = $date->translatedFormat('d M'); 
        }

        $feedLogsLast7Days = FeedLogDetail::with('feedLog')
            ->whereHas('feedLog', function($q) use ($tanggalMulai) {
                $q->whereDate('tanggal_pakan', '>=', $tanggalMulai);
            })->get();

        $datasetsPakan = [];
        $pakanColors = ['#6366f1', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6', '#10b981']; 
        $pakanColorIndex = 0;

        foreach ($inventories as $item) {
            $dataPoints = [];
            foreach ($chartLabelsPakanRaw as $date) {
                $dailySum = $feedLogsLast7Days->filter(function($detail) use ($item, $date) {
                    return $detail->inventory_id === $item->id && 
                           Carbon::parse($detail->feedLog->tanggal_pakan)->format('Y-m-d') === $date;
                })->sum('jumlah_kg');
                
                $dataPoints[] = (float) $dailySum;
            }

            $datasetsPakan[] = [
                'label' => $item->nama_pakan,
                'borderColor' => $pakanColors[$pakanColorIndex % count($pakanColors)],
                'backgroundColor' => 'transparent',
                'borderWidth' => 3,
                'pointRadius' => 0,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => $pakanColors[$pakanColorIndex % count($pakanColors)],
                'tension' => 0.4,
                'data' => $dataPoints,
            ];
            $pakanColorIndex++;
        }

        $chartDataPakan = [
            'labels' => $chartLabelsPakanDisplay,
            'datasets' => $datasetsPakan
        ];

        // 4. PREDIKSI CERDAS PER JENIS PAKAN
        $inventoryList = $inventories->map(function ($item) use ($tanggalMulai, $periodeHari) {
            $totalPakai = FeedLogDetail::where('inventory_id', $item->id)
                ->whereHas('feedLog', function ($query) use ($tanggalMulai) {
                    $query->where('tanggal_pakan', '>=', $tanggalMulai);
                })
                ->sum('jumlah_kg');

            $rataRataItem = $totalPakai / $periodeHari;
            $estimasiItem = $rataRataItem > 0 ? floor($item->total_stok_kg / $rataRataItem) : 0;

            $statusItem = 'Aman';
            if ($item->total_stok_kg <= 0) $statusItem = 'Habis';
            elseif ($estimasiItem <= 3 && $rataRataItem > 0) $statusItem = 'Kritis';
            elseif ($estimasiItem <= 7 && $rataRataItem > 0) $statusItem = 'Waspada';

            return [
                'id' => $item->id,
                'nama' => $item->nama_pakan,
                'stok' => $item->total_stok_kg,
                'rata_rata' => round($rataRataItem, 2),
                'estimasi' => $estimasiItem,
                'status' => $statusItem
            ];
        });

        // 5. GRAFIK PERTUMBUHAN IKAN (HANYA SIKLUS AKTIF)
        $datasetsBerat = [];
        $chartLabelsBerat = [];
        $colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#f43f5e', '#06b6d4', '#84cc16', '#a855f7']; 
        $colorIndex = 0;

        // Ambil kolam yang PUNYA siklus aktif saja
        $kolamAktif = $kolams->filter(function($k) { return $k->siklusAktif != null; });

        if ($kolamAktif->count() > 0) {
            $kolamAktifIds = $kolamAktif->pluck('id')->toArray();

            // Tarik data sample HANYA dari kolam aktif dan SETELAH tanggal mulai siklus
            $dataSample = DailyParameter::whereIn('kolam_id', $kolamAktifIds)
                ->whereNotNull('berat_sample')
                ->orderBy('tanggal_cek', 'asc')
                ->get()
                ->filter(function($sample) use ($kolamAktif) {
                    $kolam = $kolamAktif->firstWhere('id', $sample->kolam_id);
                    return $sample->tanggal_cek >= $kolam->siklusAktif->tanggal_mulai;
                })->values();

            $uniqueDates = $dataSample->pluck('tanggal_cek')->unique()->sort()->values();
            
            $chartLabelsBerat = $uniqueDates->map(function($date) {
                return Carbon::parse($date)->translatedFormat('d M');
            })->toArray();

            foreach ($kolamAktif as $kolam) {
                $samples = $dataSample->where('kolam_id', $kolam->id);
                $dataPoints = [];
                
                foreach ($uniqueDates as $date) {
                    $sampleOnDate = $samples->firstWhere('tanggal_cek', $date);
                    $dataPoints[] = $sampleOnDate ? (float) $sampleOnDate->berat_sample : null;
                }

                $datasetsBerat[] = [
                    'label' => $kolam->nama_kolam,
                    'borderColor' => $colors[$colorIndex % count($colors)],
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 3,
                    'pointRadius' => 0, 
                    'pointHoverRadius' => 6, 
                    'pointBackgroundColor' => $colors[$colorIndex % count($colors)],
                    'spanGaps' => true, 
                    'tension' => 0.4,
                    'data' => $dataPoints,
                ];
                $colorIndex++;
            }
        }

        $chartDataBerat = [
            'labels' => $chartLabelsBerat,
            'datasets' => $datasetsBerat,
        ];

        // =========================================================================
        // 6. RENDER KE VUE (BAGIAN INI YANG SEBELUMNYA HILANG)
        // =========================================================================
        return Inertia::render('Dashboard', [
            'ringkasan' => [
                'totalKolam' => $totalKolam,
                'totalIkan' => $totalIkan,
                'totalBiomassaKg' => round($totalBiomassaKg, 2),
            ],
            'kolam_list' => $kolamList, 
            'inventory' => [
                'stokPakan' => round($stokPakanGlobal, 2), 
                'namaPakan' => $namaPakanGlobal,
            ],
            'inventory_list' => $inventoryList, 
            'chartPakan' => $chartDataPakan, 
            'chartBerat' => $chartDataBerat
        ]);
    }
}