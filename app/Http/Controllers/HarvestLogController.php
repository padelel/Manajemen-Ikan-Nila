<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\ParameterHarian;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HarvestLogController extends Controller
{
    public function index(Request $request)
    {
        $query = HarvestLog::with(['kolam', 'user']);

        if (
            $request->filled('jenis_panen') &&
            $request->jenis_panen !== 'Semua'
        ) {
            $query->where('jenis_panen', match ($request->jenis_panen) {
                'Parsial' => 'parsial',
                'Full' => 'total',
                default => $request->jenis_panen,
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_panen', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_panen', '<=', $request->tanggal_akhir);
        }

        $logs = $query->orderBy('tanggal_panen', 'desc')->get();

        return Inertia::render('HarvestLog/Index', [
            'logs' => $logs,
            'filters' => $request->only([
                'jenis_panen',
                'tanggal_mulai',
                'tanggal_akhir',
            ]),
        ]);
    }

    public function show(HarvestLog $panen)
    {
        $panen->load(['kolam', 'user']);

        $tanggalPanen = Carbon::parse($panen->tanggal_panen);

        // Cari siklus yang berkaitan: untuk panen Full, siklus sudah ditutup
        // dengan tanggal_selesai = tanggal_panen. Untuk parsial, siklus masih aktif.
        $siklus = null;
        if ($panen->jenis_panen === 'total') {
            $siklus = SiklusBudidaya::where('kolam_id', $panen->kolam_id)
                ->whereDate('tanggal_selesai', $tanggalPanen)
                ->where('status_aktif', 'selesai')
                ->first();
        }

        // Fallback: ambil siklus yang dimulai sebelum tanggal panen
        if (! $siklus) {
            $siklus = SiklusBudidaya::where('kolam_id', $panen->kolam_id)
                ->whereDate('tanggal_mulai', '<=', $tanggalPanen)
                ->latest('tanggal_mulai')
                ->first();
        }

        $tanggalMulaiSiklus = $siklus
            ? Carbon::parse($siklus->tanggal_mulai)
            : null;

        // Tebar awal dalam siklus ini
        $tebarLog = TebarLog::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate(
                    'tanggal_tebar',
                    '>=',
                    $tanggalMulaiSiklus,
                ),
            )
            ->orderBy('tanggal_tebar')
            ->first();

        // Semua panen (parsial + full) dalam rentang siklus ini
        $riwayatPanen = HarvestLog::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate(
                    'tanggal_panen',
                    '>=',
                    $tanggalMulaiSiklus,
                ),
            )
            ->whereDate('tanggal_panen', '<=', $tanggalPanen)
            ->with('user')
            ->orderBy('tanggal_panen')
            ->get();

        // Semua kematian dalam rentang siklus ini
        $mortalityLogs = MortalityLog::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate(
                    'tanggal_kematian',
                    '>=',
                    $tanggalMulaiSiklus,
                ),
            )
            ->whereDate('tanggal_kematian', '<=', $tanggalPanen)
            ->with('user')
            ->orderBy('tanggal_kematian')
            ->get();

        $parameterHarians = ParameterHarian::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate(
                    'tanggal_cek',
                    '>=',
                    $tanggalMulaiSiklus,
                ),
            )
            ->whereDate('tanggal_cek', '<=', $tanggalPanen)
            ->orderBy('tanggal_cek')
            ->get();

        $parameterChart = [
            'labels' => $parameterHarians->map(fn ($item) => Carbon::parse($item->tanggal_cek)->format('d M'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $parameterHarians->pluck('suhu')->toArray(),
                    'borderColor' => '#f43f5e',
                    'backgroundColor' => '#f43f5e',
                    'fill' => false,
                ],
                [
                    'label' => 'pH',
                    'data' => $parameterHarians->pluck('ph')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                    'fill' => false,
                ],
                [
                    'label' => 'DO (mg/L)',
                    'data' => $parameterHarians->pluck('do_mgl')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'fill' => false,
                ],
                [
                    'label' => 'Amonia (mg/L)',
                    'data' => $parameterHarians->pluck('amonia_mgl')->toArray(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => '#f97316',
                    'fill' => false,
                ],
                [
                    'label' => 'Flok (ml)',
                    'data' => $parameterHarians->pluck('flok_ml')->toArray(),
                    'borderColor' => '#a855f7',
                    'backgroundColor' => '#a855f7',
                    'fill' => false,
                ],
                [
                    'label' => 'Kecerahan (cm)',
                    'data' => $parameterHarians->pluck('kecerahan_cm')->toArray(),
                    'borderColor' => '#eab308',
                    'backgroundColor' => '#eab308',
                    'fill' => false,
                ],
            ],
        ];

        $tiketQuery = Tiket::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulaiSiklus),
            )
            ->whereDate('created_at', '<=', $tanggalPanen);

        $totalTiket = $tiketQuery->count();
        $tiketSelesai = Tiket::where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate('created_at', '>=', $tanggalMulaiSiklus),
            )
            ->whereDate('created_at', '<=', $tanggalPanen)
            ->where('status', 'selesai')
            ->count();

        $totalPakanKg = null;

        // Kalkulasi statistik kinerja siklus
        $totalBiomassaPanenKg = $riwayatPanen->sum('berat_total_kg');
        $totalEkorPanen = $riwayatPanen->sum('jumlah_ikan_panen');
        $totalMati = $mortalityLogs->sum('jumlah_mati');

        $durasiHari = $tanggalMulaiSiklus
            ? (int) $tanggalMulaiSiklus->diffInDays($tanggalPanen)
            : null;

        $survivalRate =
            $siklus && $siklus->jumlah_tebar_awal > 0
                ? round(($totalEkorPanen / $siklus->jumlah_tebar_awal) * 100, 1)
                : null;

        return Inertia::render('HarvestLog/Show', [
            'harvest' => $panen,
            'siklus' => $siklus,
            'tebarLog' => $tebarLog,
            'statistik' => [
                'durasi_hari' => $durasiHari,
                'jumlah_tebar_awal' => $siklus?->jumlah_tebar_awal,
                'total_mati' => $totalMati,
                'total_ekor_panen' => $totalEkorPanen,
                'total_biomassa_panen_kg' => round($totalBiomassaPanenKg, 2),
                'survival_rate_persen' => $survivalRate,
                'total_tiket' => $totalTiket,
                'tiket_selesai' => $tiketSelesai,
            ],
            'parameter_chart' => $parameterChart,
            'riwayat_panen' => $riwayatPanen,
            'mortality_logs' => $mortalityLogs,
        ]);
    }

    public function create()
    {
        // Hanya tampilkan kolam yang memiliki siklus aktif ('berjalan')
        $kolams = Kolam::whereHas('siklusBudidayas', fn ($q) => $q->where('status_aktif', 'berjalan'))
            ->get()
            ->map(function ($kolam) {
                $siklus = SiklusBudidaya::where('kolam_id', $kolam->id)
                    ->where('status_aktif', 'berjalan')
                    ->latest('tanggal_mulai')
                    ->first();

                $totalMati = $siklus
                    ? MortalityLog::where('kolam_id', $kolam->id)
                        ->whereDate('tanggal_kematian', '>=', $siklus->tanggal_mulai)
                        ->sum('jumlah_mati')
                    : 0;

                $kolam->populasi_saat_ini = $siklus
                    ? max(0, $siklus->jumlah_tebar_awal - $totalMati)
                    : 0;

                $kolam->active_siklus_id = $siklus?->id;

                return $kolam;
            });

        return Inertia::render('HarvestLog/Create', ['kolams' => $kolams]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jenis_panen' => 'required|in:Full',
            'tanggal_panen' => 'required|date',
            'jumlah_ikan' => 'required|integer|min:1',
            'berat_total_kg' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string',
        ]);

        $siklusBudidaya = SiklusBudidaya::where('kolam_id', $validated['kolam_id'])
            ->where('status_aktif', 'berjalan')
            ->latest('tanggal_mulai')
            ->first();

        if (! $siklusBudidaya) {
            return back()
                ->withErrors(['kolam_id' => 'Kolam yang dipilih tidak memiliki siklus aktif.'])
                ->withInput();
        }

        $currentPopulation = max(0, $siklusBudidaya->jumlah_tebar_awal - MortalityLog::where('kolam_id', $validated['kolam_id'])
            ->whereDate('tanggal_kematian', '>=', $siklusBudidaya->tanggal_mulai)
            ->sum('jumlah_mati'));

        if ((int) $validated['jumlah_ikan'] !== $currentPopulation) {
            return back()
                ->withErrors(['jumlah_ikan' => 'Jumlah ikan harus sesuai dengan populasi aktif kolam.'])
                ->withInput();
        }

        $validated['siklus_budidaya_id'] = $siklusBudidaya->id;
        $validated['jenis_panen'] = 'total';
        $validated['jumlah_ikan_panen'] = $validated['jumlah_ikan'];
        unset($validated['jumlah_ikan']);

        DB::beginTransaction();

        try {
            $validated['user_id'] = Auth::id();
            HarvestLog::create($validated);

            // Tutup siklus aktif jika panen full
            if ($validated['jenis_panen'] === 'total') {
                SiklusBudidaya::where('kolam_id', $validated['kolam_id'])
                    ->where('status_aktif', 'berjalan')
                    ->update([
                        'tanggal_selesai' => $validated['tanggal_panen'],
                        'status_aktif' => 'selesai',
                    ]);
            }

            DB::commit();

            return redirect()->route('panen.index')->with('success', 'Data panen berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: '.$e->getMessage()])
                ->withInput();
        }
    }
}
