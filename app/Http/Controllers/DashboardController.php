<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\FeedLog;
use App\Models\DailyParameter; // Tambahkan ini untuk chart berat
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Pastikan DB Facade dipanggil

class DashboardController extends Controller
{
    public function index()
    {
        // 1. DATA RINGKASAN ASET & BIOMASSA
        $kolams = Kolam::all();
        $totalKolam = $kolams->count();
        $totalIkan = $kolams->sum('jumlah_ikan');
        
        // Menghitung Total Estimasi Biomassa Ikan (Kg) dari diskusi sebelumnya
        $totalBiomassaKg = $kolams->sum(function ($kolam) {
            return ($kolam->jumlah_ikan * $kolam->berat_rata_gram) / 1000;
        });

        // 2. DATA INVENTORI PAKAN
        $inventory = Inventory::first();
        $stokPakan = $inventory ? $inventory->total_stok_kg : 0;
        $namaPakan = $inventory ? $inventory->nama_pakan : 'Belum ada pakan';

        // 3. AMBIL DATA PENGGUNAAN PAKAN 7 HARI TERAKHIR (SMA)
        $periodeHari = 7;
        $tanggalMulai = Carbon::today()->subDays($periodeHari - 1); 

        $riwayatPakan = FeedLog::select(DB::raw('DATE(tanggal_pakan) as tanggal'), DB::raw('SUM(pakan_aktual) as total_harian'))
            ->where('tanggal_pakan', '>=', $tanggalMulai)
            ->groupBy('tanggal')
            ->get();

        $totalPengeluaran7Hari = $riwayatPakan->sum('total_harian');
        $rataRataHarian = $totalPengeluaran7Hari / $periodeHari;
        $estimasiHari = $rataRataHarian > 0 ? floor($stokPakan / $rataRataHarian) : 0;

        // 4. STATUS SPK STOK PAKAN
        $statusStok = 'Aman';
        if ($stokPakan <= 0) $statusStok = 'Habis';
        elseif ($estimasiHari <= 3 && $rataRataHarian > 0) $statusStok = 'Kritis';
        elseif ($estimasiHari <= 7 && $rataRataHarian > 0) $statusStok = 'Waspada';

        // 5. FORMAT DATA GRAFIK PAKAN (CHART.JS)
        $chartLabelsPakan = [];
        $chartDataPakan = [];

        // Looping mundur agar tanggal urut
        for ($i = $periodeHari - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $chartLabelsPakan[] = Carbon::parse($date)->translatedFormat('d M'); 
            
            $feedData = $riwayatPakan->firstWhere('tanggal', $date);
            $chartDataPakan[] = $feedData ? (float) $feedData->total_harian : 0;
        }

        // 6. FORMAT DATA GRAFIK PERTUMBUHAN IKAN (CHART.JS)
        $dataSample = DailyParameter::whereNotNull('berat_sample')->orderBy('tanggal_cek', 'asc')->get();
        $chartDataBerat = [
            // Format tanggal agar sama estetikanya (Contoh: 12 Mar)
            'labels' => $dataSample->pluck('tanggal_cek')->map(function($date) {
                return Carbon::parse($date)->translatedFormat('d M');
            }),
            'berat'  => $dataSample->pluck('berat_sample'),
        ];

        // 7. RENDER KE VUE (Dikelompokkan agar rapi)
        return Inertia::render('Dashboard', [
            'ringkasan' => [
                'totalKolam' => $totalKolam,
                'totalIkan' => $totalIkan,
                'totalBiomassaKg' => round($totalBiomassaKg, 2),
            ],
            'inventory' => [
                'stokPakan' => $stokPakan,
                'namaPakan' => $namaPakan,
            ],
            'sma' => [
                'rata_rata_harian' => round($rataRataHarian, 2),
                'estimasi_hari' => $estimasiHari,
                'status' => $statusStok
            ],
            'chartPakan' => [
                'labels' => $chartLabelsPakan,
                'data' => $chartDataPakan
            ],
            'chartBerat' => $chartDataBerat
        ]);
    }
}