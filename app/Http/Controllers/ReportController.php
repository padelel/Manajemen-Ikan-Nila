<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\HarvestLog;
use App\Models\MortalityLog;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Data Agregat Global
        $totalPanenKg = HarvestLog::sum('berat_total_kg');
        $totalKematian = MortalityLog::sum('jumlah_mati');
        
        // 2. Data Rasio Tiket Mitigasi (Selesai vs Belum)
        $tiketSelesai = Tiket::where('status', 'selesai')->count();
        $tiketAktif = Tiket::whereIn('status', ['open', 'in_progress', 'menunggu_verifikasi'])->count();

        // 3. Data Performa per Kolam
        $performaKolam = Kolam::with(['harvestLogs', 'tikets', 'mortalityLogs'])
            ->get()
            ->map(function ($kolam) {
                return [
                    'id' => $kolam->id,
                    'nama_kolam' => $kolam->nama_kolam,
                    'total_panen_kg' => $kolam->harvestLogs->sum('berat_total_kg'),
                    'total_kematian' => $kolam->mortalityLogs->sum('jumlah_mati'),
                    'jumlah_masalah_air' => $kolam->tikets->count(),
                ];
            });

        return Inertia::render('Analisis/Index', [
            'statistik' => [
                'total_panen_kg' => (float) $totalPanenKg,
                'total_kematian_ekor' => (int) $totalKematian,
                'tiket_selesai' => $tiketSelesai,
                'tiket_aktif' => $tiketAktif,
            ],
            'performa_kolam' => $performaKolam
        ]);
    }
}