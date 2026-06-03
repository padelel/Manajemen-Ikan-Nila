<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
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
            $query->where('jenis_panen', $request->jenis_panen);
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
        if ($panen->jenis_panen === 'Full') {
            $siklus = SiklusBudidaya::where('kolam_id', $panen->kolam_id)
                ->whereDate('tanggal_selesai', $tanggalPanen)
                ->where('status_aktif', 'Selesai')
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

        // Total pakan yang digunakan selama siklus ini
        $totalPakanKg = DB::table('feed_logs')
            ->where('kolam_id', $panen->kolam_id)
            ->when(
                $tanggalMulaiSiklus,
                fn ($q) => $q->whereDate(
                    'tanggal_pakan',
                    '>=',
                    $tanggalMulaiSiklus->format('Y-m-d'),
                ),
            )
            ->whereDate('tanggal_pakan', '<=', $tanggalPanen->format('Y-m-d'))
            ->sum('pakan_aktual');

        // Kalkulasi statistik kinerja siklus
        $totalBiomassaPanenKg = $riwayatPanen->sum('berat_total_kg');
        $totalEkorPanen = $riwayatPanen->sum('jumlah_ikan');
        $totalMati = $mortalityLogs->sum('jumlah_mati');

        $beratRataAkhirGram =
            $totalEkorPanen > 0
                ? round(($totalBiomassaPanenKg * 1000) / $totalEkorPanen, 1)
                : 0;

        $durasiHari = $tanggalMulaiSiklus
            ? (int) $tanggalMulaiSiklus->diffInDays($tanggalPanen)
            : null;

        $survivalRate =
            $siklus && $siklus->jumlah_tebar_awal > 0
                ? round(($totalEkorPanen / $siklus->jumlah_tebar_awal) * 100, 1)
                : null;

        // FCR (Feed Conversion Ratio) — semakin kecil semakin efisien
        $fcr =
            $totalBiomassaPanenKg > 0
                ? round($totalPakanKg / $totalBiomassaPanenKg, 2)
                : null;

        // ADG (Average Daily Growth) dalam gram/hari
        $beratAwalGram = $tebarLog
            ? floatval($tebarLog->berat_rata_gram)
            : null;
        $adg =
            $durasiHari &&
            $durasiHari > 0 &&
            $beratAwalGram &&
            $beratRataAkhirGram > 0
                ? round(($beratRataAkhirGram - $beratAwalGram) / $durasiHari, 2)
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
                'berat_awal_tebar_gram' => $beratAwalGram,
                'berat_rata_panen_gram' => $beratRataAkhirGram,
                'survival_rate_persen' => $survivalRate,
                'fcr' => $fcr,
                'adg_gram_hari' => $adg,
                'total_pakan_kg' => round($totalPakanKg, 1),
            ],
            'riwayat_panen' => $riwayatPanen,
            'mortality_logs' => $mortalityLogs,
        ]);
    }

    public function create()
    {
        $kolams = Kolam::where('jumlah_ikan', '>', 0)->get();

        return Inertia::render('HarvestLog/Create', ['kolams' => $kolams]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jenis_panen' => 'required|in:Parsial,Full',
            'tanggal_panen' => 'required|date',
            'jumlah_ikan' => 'required|integer|min:1',
            'berat_total_kg' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $kolam = Kolam::findOrFail($validated['kolam_id']);

            if ($validated['jumlah_ikan'] > $kolam->jumlah_ikan) {
                return back()
                    ->withErrors([
                        'jumlah_ikan' => 'Jumlah ikan yang dipanen ('.
                            $validated['jumlah_ikan'].
                            ') melebihi total populasi kolam saat ini ('.
                            $kolam->jumlah_ikan.
                            ').',
                    ])
                    ->withInput();
            }

            if (
                $validated['jenis_panen'] === 'Parsial' &&
                $validated['jumlah_ikan'] == $kolam->jumlah_ikan
            ) {
                $validated['jenis_panen'] = 'Full';
                $validated['catatan'] =
                    ($validated['catatan'] ?? '').
                    ' (Sistem Otomatis: Diubah menjadi panen Full karena memanen seluruh sisa populasi kolam).';
            }

            $validated['user_id'] = Auth::id();
            HarvestLog::create($validated);

            if ($validated['jenis_panen'] === 'Full') {
                $kolam->update([
                    'jumlah_ikan' => 0,
                    'berat_rata_gram' => 0,
                ]);

                $siklusAktif = SiklusBudidaya::where('kolam_id', $kolam->id)
                    ->where('status_aktif', 'Aktif')
                    ->first();

                if ($siklusAktif) {
                    $siklusAktif->update([
                        'tanggal_selesai' => $validated['tanggal_panen'],
                        'status_aktif' => 'Selesai',
                    ]);
                }
            } else {
                $kolam->decrement('jumlah_ikan', $validated['jumlah_ikan']);
            }

            DB::commit();

            return redirect()
                ->route('panen.index')
                ->with('success', 'Data panen berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => 'Terjadi kesalahan saat menyimpan data: '.
                        $e->getMessage(),
                ])
                ->withInput();
        }
    }
}
