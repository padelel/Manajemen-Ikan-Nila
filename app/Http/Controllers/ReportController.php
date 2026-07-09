<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\Tiket;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Data Agregat Global
        $totalPanenKg = HarvestLog::sum('berat_total_kg');
        $totalKematian = MortalityLog::sum('jumlah_mati');
        $tiketSelesai = Tiket::where('status', 'selesai')->count();
        $tiketAktif = Tiket::whereNotIn('status', ['selesai'])->count();

        // 2. Data Performa per Siklus Budidaya
        $performaSiklus = SiklusBudidaya::with(['kolam'])
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(function ($siklus) {
                // Tentukan batas waktu penghitungan log untuk siklus ini
                $waktuMulai = $siklus->tanggal_mulai;
                $waktuSelesai = $siklus->status_aktif === 'selesai' ? $siklus->updated_at : Carbon::now();

                // Hitung total kematian dalam rentang waktu siklus
                $kematian = MortalityLog::where('kolam_id', $siklus->kolam_id)
                    ->whereBetween('tanggal_kematian', [$waktuMulai, $waktuSelesai])
                    ->sum('jumlah_mati');

                // Hitung total panen dalam rentang waktu siklus
                $panenKg = HarvestLog::where('kolam_id', $siklus->kolam_id)
                    ->whereBetween('tanggal_panen', [$waktuMulai, $waktuSelesai])
                    ->sum('berat_total_kg');

                // Hitung Survival Rate (SR)
                $sisaIkan = $siklus->jumlah_tebar_awal - $kematian;
                $sr = $siklus->jumlah_tebar_awal > 0
                    ? round(($sisaIkan / $siklus->jumlah_tebar_awal) * 100, 2)
                    : 0;

                // Hitung Rasio Penyelesaian Tiket Mitigasi AI
                $tikets = Tiket::where('kolam_id', $siklus->kolam_id)
                    ->whereBetween('created_at', [$waktuMulai, $waktuSelesai])
                    ->get();

                return [
                    'id' => $siklus->id,
                    'nama_kolam' => $siklus->kolam ? $siklus->kolam->nama_kolam : 'Kolam Dihapus',
                    'status_siklus' => $siklus->status_aktif,
                    'tanggal_mulai' => $siklus->tanggal_mulai,
                    'tebar_awal' => $siklus->jumlah_tebar_awal,
                    'total_kematian' => (int) $kematian,
                    'survival_rate' => $sr,
                    'total_panen_kg' => (float) $panenKg,
                    'total_tiket' => $tikets->count(),
                    'tiket_selesai' => $tikets->where('status', 'selesai')->count(),
                ];
            });

        return Inertia::render('Analisis/Index', [
            'statistik' => [
                'total_panen_kg' => (float) $totalPanenKg,
                'total_kematian_ekor' => (int) $totalKematian,
                'tiket_selesai' => $tiketSelesai,
                'tiket_aktif' => $tiketAktif,
            ],
            'performa_siklus' => $performaSiklus,
        ]);
    }
}
