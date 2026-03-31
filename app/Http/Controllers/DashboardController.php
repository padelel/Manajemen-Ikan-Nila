<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\FeedLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. DATA RINGKASAN ASET
        $totalKolam = Kolam::count();
        $totalIkan = Kolam::sum('jumlah_ikan');
        
        $inventory = Inventory::first();
        $stokPakan = $inventory ? $inventory->total_stok_kg : 0;
        $namaPakan = $inventory ? $inventory->nama_pakan : 'Belum ada pakan';

        // 2. AMBIL DATA 7 HARI TERAKHIR
        $periodeHari = 7;
        $tanggalMulai = Carbon::today()->subDays($periodeHari - 1); // 7 hari termasuk hari ini

        $riwayatPakan = FeedLog::select(DB::raw('DATE(tanggal_pakan) as tanggal'), DB::raw('SUM(pakan_aktual) as total_harian'))
            ->where('tanggal_pakan', '>=', $tanggalMulai)
            ->groupBy('tanggal')
            ->get();

        $totalPengeluaran7Hari = $riwayatPakan->sum('total_harian');
        $rataRataHarian = $totalPengeluaran7Hari / $periodeHari;
        $estimasiHari = $rataRataHarian > 0 ? floor($stokPakan / $rataRataHarian) : 0;

        // 3. STATUS SPK
        $statusStok = 'Aman';
        if ($stokPakan <= 0) $statusStok = 'Habis';
        elseif ($estimasiHari <= 3 && $rataRataHarian > 0) $statusStok = 'Kritis';
        elseif ($estimasiHari <= 7 && $rataRataHarian > 0) $statusStok = 'Waspada';

        // 4. FORMAT DATA UNTUK CHART.JS
        $chartLabels = [];
        $chartData = [];

        // Looping mundur 7 hari agar di grafik tanggalnya urut dan tidak ada yang bolong
        for ($i = $periodeHari - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $chartLabels[] = Carbon::parse($date)->translatedFormat('d M'); // Contoh: 21 Mar
            
            $feedData = $riwayatPakan->firstWhere('tanggal', $date);
            $chartData[] = $feedData ? (float) $feedData->total_harian : 0;
        }

        return Inertia::render('Dashboard', [
            'totalKolam' => $totalKolam,
            'totalIkan' => $totalIkan,
            'stokPakan' => $stokPakan,
            'namaPakan' => $namaPakan,
            'sma' => [
                'rata_rata_harian' => round($rataRataHarian, 2),
                'estimasi_hari' => $estimasiHari,
                'status' => $statusStok
            ],
            'chartData' => [
                'labels' => $chartLabels,
                'data' => $chartData
            ]
        ]);
    }
}