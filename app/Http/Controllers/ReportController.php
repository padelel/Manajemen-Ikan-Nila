<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $kolams = Kolam::withCount(['siklusBudidayas as total_siklus'])->get()->map(function ($kolam) {
            return [
                'id' => $kolam->id,
                'nama_kolam' => $kolam->nama_kolam,
                'total_siklus' => (int) $kolam->total_siklus,
            ];
        });

        return Inertia::render('Analisis/Index', [
            'kolams' => $kolams,
        ]);
    }

    public function show($id)
    {
        $kolam = Kolam::findOrFail($id);

        // Ambil seluruh siklus budidaya pada kolam ini, urutkan dari yang terlama
        $siklusBudidaya = SiklusBudidaya::where('kolam_id', $id)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        $dataSiklus = [];
        $counter = 1;

        foreach ($siklusBudidaya as $siklus) {
            $waktuMulai = $siklus->tanggal_mulai;
            // Batas akhir: jika siklus sudah panen, gunakan tanggal panen/update terakhir. Jika belum, gunakan hari ini.
            $waktuSelesai = $siklus->status_aktif === 'selesai' ? ($siklus->tanggal_selesai ?? $siklus->updated_at) : Carbon::now();

            // Ambil Data Panen (Karena panen total, cukup ambil jumlah rentang waktu siklus ini)
            $panen = HarvestLog::where('kolam_id', $kolam->id)
                ->whereBetween('tanggal_panen', [$waktuMulai, $waktuSelesai])
                ->first();

            $tanggalPanen = $panen?->tanggal_panen;
            $jumlahIkanPanen = $panen?->jumlah_ikan_panen ?? 0;
            $sr = $panen?->survival_rate ?? 0;

            // Kalkulasi Durasi (Hari)
            if ($tanggalPanen) {
                $durasi = Carbon::parse($waktuMulai)->diffInDays(Carbon::parse($tanggalPanen));
            } else {
                $durasi = Carbon::parse($waktuMulai)->diffInDays($waktuSelesai);
            }

            // Ambil Kematian per Hari
            $mortality = MortalityLog::where('kolam_id', $kolam->id)
                ->whereBetween('tanggal_kematian', [$waktuMulai, $waktuSelesai])
                ->select(DB::raw('DATE(tanggal_kematian) as tanggal'), DB::raw('SUM(jumlah_mati) as total_mati'))
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();

            $totalMati = $mortality->sum('total_mati');

            // Ambil Tiket AI
            $tikets = Tiket::where('kolam_id', $kolam->id)
                ->whereBetween('created_at', [$waktuMulai, $waktuSelesai])
                ->get();

            // Ambil Parameter Kualitas Air
            $parameterAir = \App\Models\ParameterHarian::where('kolam_id', $kolam->id)
                ->whereBetween('tanggal_cek', [$waktuMulai, $waktuSelesai])
                ->orderBy('tanggal_cek', 'asc')
                ->get();

            $dataSiklus[] = [
                'id' => $siklus->id,
                'nama_siklus' => 'Siklus '.$counter,
                'status' => $siklus->status_aktif,
                'tanggal_mulai' => $waktuMulai,
                'tanggal_panen' => $tanggalPanen,
                'durasi_hari' => $durasi,
                'sumber_benih' => $siklus->catatan ?? 'Tidak dicatat',
                'tebar_awal' => $siklus->jumlah_tebar_awal,
                'jumlah_ikan_panen' => (int) $jumlahIkanPanen,
                'total_kematian' => (int) $totalMati,
                'sr' => $siklus->jumlah_tebar_awal > 0 ? round((($siklus->jumlah_tebar_awal - $totalMati) / $siklus->jumlah_tebar_awal) * 100, 2) : 0,
                'sr_panen' => (float) $sr,
                'riwayat_kematian' => [
                    'labels' => $mortality->pluck('tanggal')->map(fn ($d) => Carbon::parse($d)->format('d M Y'))->toArray(),
                    'data' => $mortality->pluck('total_mati')->toArray(),
                ],
                'mitigasi' => [
                    'total' => $tikets->count(),
                    'selesai' => $tikets->where('status', 'selesai')->count(),
                ],
                'grafik_air' => [
                    'labels' => $parameterAir->pluck('tanggal_cek')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
                    'suhu' => $parameterAir->pluck('suhu')->toArray(),
                    'ph' => $parameterAir->pluck('ph')->toArray(),
                    'do' => $parameterAir->pluck('do_mgl')->toArray(),
                    'kecerahan' => $parameterAir->pluck('kecerahan_cm')->toArray(),
                    'flok' => $parameterAir->pluck('flok_ml')->toArray(),
                    'amonia' => $parameterAir->pluck('amonia_mgl')->toArray(),
                ],
            ];
            $counter++;
        }

        return Inertia::render('Analisis/Show', [
            'kolam' => $kolam,
            // Kita reverse (balik) agar siklus yang paling baru muncul di urutan pertama (Tab awal)
            'dataSiklus' => array_reverse($dataSiklus),
        ]);
    }
}
