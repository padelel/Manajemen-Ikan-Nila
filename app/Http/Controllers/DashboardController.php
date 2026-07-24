<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\ParameterHarian;
use App\Models\SiklusBudidaya;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isOperator = $user?->role === 'operator';

        if ($isOperator) {
            $kolams = $user->kolams()->get();
            $assignedKolamIds = $kolams->pluck('id')->toArray();
        } else {
            $kolams = Kolam::all();
            $assignedKolamIds = null;
        }

        // Labels 7 hari terakhir
        $labels7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels7Hari[] = Carbon::today()->subDays($i)->translatedFormat('d M');
        }

        $grafikPerKolam = [];

        foreach ($kolams as $kolam) {
            // Siklus aktif kolam ini
            $siklus = SiklusBudidaya::where('kolam_id', $kolam->id)
                ->where('status_aktif', 'berjalan')
                ->latest('tanggal_mulai')
                ->first();

            // ── Kualitas Air 7 hari terakhir ─────────────────────────
            $dataAir = ['suhu' => [], 'ph' => [], 'do_mgl' => [], 'amonia_mgl' => [], 'flok_ml' => []];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $param = ParameterHarian::where('kolam_id', $kolam->id)
                    ->whereDate('tanggal_cek', $date)
                    ->latest()
                    ->first();

                $dataAir['suhu'][] = $param?->suhu;
                $dataAir['ph'][] = $param?->ph;
                $dataAir['do_mgl'][] = $param?->do_mgl;
                $dataAir['amonia_mgl'][] = $param?->amonia_mgl;
                $dataAir['flok_ml'][] = $param?->flok_ml;
            }

            // ── Populasi sejak awal siklus ───────────────────────────
            $popLabels = [];
            $popData = [];

            if ($siklus) {
                $popLabels[] = Carbon::parse($siklus->tanggal_mulai)->format('d M');
                $popData[] = $siklus->jumlah_tebar_awal;
                $currentPop = $siklus->jumlah_tebar_awal;

                $kematianPerHari = DB::table('mortality_logs')
                    ->where('kolam_id', $kolam->id)
                    ->where('siklus_budidaya_id', $siklus->id)
                    ->select(
                        DB::raw('DATE(tanggal_kematian) as tanggal'),
                        DB::raw('SUM(jumlah_mati) as total_mati')
                    )
                    ->groupBy('tanggal')
                    ->orderBy('tanggal')
                    ->get();

                foreach ($kematianPerHari as $k) {
                    $currentPop -= $k->total_mati;
                    $popLabels[] = Carbon::parse($k->tanggal)->format('d M');
                    $popData[] = max(0, $currentPop);
                }
            }

            $totalMati = $siklus
                ? MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati')
                : 0;

            $populasiTerkini = $siklus
                ? max(0, $siklus->jumlah_tebar_awal - $totalMati)
                : null;

            $grafikPerKolam[] = [
                'kolam' => [
                    'id' => $kolam->id,
                    'nama_kolam' => $kolam->nama_kolam,
                    'lokasi' => $kolam->lokasi,
                ],
                'siklus' => $siklus ? [
                    'tanggal_mulai' => Carbon::parse($siklus->tanggal_mulai)->format('d M Y'),
                    'jumlah_tebar_awal' => $siklus->jumlah_tebar_awal,
                    'populasi_terkini' => $populasiTerkini,
                ] : null,
                'kualitasAir' => [
                    'labels' => $labels7Hari,
                    'suhu' => $dataAir['suhu'],
                    'ph' => $dataAir['ph'],
                    'do_mgl' => $dataAir['do_mgl'],
                    'amonia_mgl' => $dataAir['amonia_mgl'],
                    'flok_ml' => $dataAir['flok_ml'],

                ],
                'populasi' => $siklus ? [
                    'labels' => $popLabels,
                    'data' => $popData,
                ] : null,
            ];
        }

        $isOperator = auth()->user()?->role === 'operator';
        $assignedKolamCount = $isOperator ? auth()->user()->kolams()->count() : null;

        return Inertia::render('Dashboard', [
            'grafikPerKolam' => $grafikPerKolam,
            'ringkasan' => [
                'totalKolam' => $kolams->count(),
                'siklusAktif' => SiklusBudidaya::where('status_aktif', 'berjalan')
                    ->when($isOperator, fn ($q) => $q->whereIn('kolam_id', $assignedKolamIds))
                    ->count(),
                'tiketAktif' => Tiket::whereNotIn('status', ['selesai'])
                    ->when($isOperator, fn ($q) => $q->whereIn('kolam_id', $assignedKolamIds))
                    ->count(),
            ],
            'isOperator' => $isOperator,
            'assignedKolamCount' => $assignedKolamCount,
            'assignedKolamNote' => $isOperator ? 'Dashboard ini hanya menampilkan kolam yang ditugaskan oleh supervisor kepada Anda.' : null,
        ]);
    }
}
