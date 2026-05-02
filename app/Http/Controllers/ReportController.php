<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Kolam;
use App\Models\DailyParameter;
use App\Models\MortalityLog;
use App\Models\HarvestLog;
use App\Models\TebarLog;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $kolams = Kolam::all();
        
        // Ambil kolam_id dari parameter URL, jika tidak ada, gunakan kolam pertama sebagai default
        $selectedKolamId = $request->kolam_id ?? ($kolams->first()->id ?? null);
        $selectedKolam = Kolam::find($selectedKolamId);

        $labels7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels7Hari[] = Carbon::today()->subDays($i)->translatedFormat('d M');
        }

        $dataBeratAktual = [];
        $dataPopulasi = [];
        
        if ($selectedKolam) {
            // =========================================================
            // 1. DATA KURVA PERTUMBUHAN (SPESIFIK KOLAM)
            // =========================================================
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $avgBerat = DailyParameter::where('kolam_id', $selectedKolamId)
                                          ->whereDate('tanggal_cek', $date)
                                          ->avg('berat_sample');
                $dataBeratAktual[] = $avgBerat ? round($avgBerat, 2) : 0; 
            }

            // =========================================================
            // 2. DATA PERGERAKAN POPULASI (SPESIFIK KOLAM)
            // =========================================================
            $historicalPopulasi = [];
            $runningTotal = $selectedKolam->jumlah_ikan; // Populasi kolam ini SAAT INI
            
            for ($i = 0; $i <= 6; $i++) {
                if ($i == 0) {
                    $historicalPopulasi[] = $runningTotal;
                } else {
                    $besoknya = Carbon::today()->subDays($i - 1)->format('Y-m-d');
                    
                    // Tarik data HANYA untuk kolam yang dipilih
                    $tebar = TebarLog::where('kolam_id', $selectedKolamId)->whereDate('tanggal_tebar', $besoknya)->sum('jumlah_ikan');
                    
                    // PERBAIKAN: Menggunakan kolom 'tanggal_kematian'
                    $mati = MortalityLog::where('kolam_id', $selectedKolamId)->whereDate('tanggal_kematian', $besoknya)->sum('jumlah_mati');
                    
                    $panen = HarvestLog::where('kolam_id', $selectedKolamId)->whereDate('tanggal_panen', $besoknya)->sum('jumlah_ikan');
                    
                    $runningTotal = $runningTotal - $tebar + $mati + $panen;
                    $historicalPopulasi[] = $runningTotal;
                }
            }
            $dataPopulasi = array_reverse($historicalPopulasi);
        }

        return Inertia::render('Report/Index', [
            'kolams' => $kolams,
            'selectedKolamId' => (int) $selectedKolamId,
            'kolamInfo' => $selectedKolam,
            'chartBerat' => [
                'labels' => $labels7Hari,
                'datasets' => [
                    [
                        'label' => 'Berat Rata-rata',
                        'data' => $dataBeratAktual,
                        'borderColor' => '#4f46e5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                        'fill' => true,
                        'tension' => 0.4
                    ]
                ]
            ],
            'chartPopulasi' => [
                'labels' => $labels7Hari,
                'datasets' => [
                    [
                        'label' => 'Populasi Kolam',
                        'data' => $dataPopulasi,
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                        'fill' => true,
                    ]
                ]
            ]
        ]);
    }
}