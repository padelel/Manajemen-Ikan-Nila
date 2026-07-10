<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HarvestLogController extends Controller
{
    public function index()
    {
        $sikluses = SiklusBudidaya::with('kolam')->orderBy('created_at', 'desc')->get();

        $dataSiklus = $sikluses->map(function ($siklus) {
            $waktuMulai = $siklus->tanggal_mulai;
            $waktuSelesai = $siklus->status_aktif === 'selesai' ? ($siklus->tanggal_selesai ?? $siklus->updated_at) : Carbon::now();

            $panen = HarvestLog::where('kolam_id', $siklus->kolam_id)
                ->where('siklus_budidaya_id', $siklus->id)
                ->first();

            $totalMati = MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati');

            return [
                'id' => $siklus->id,
                'kolam_id' => $siklus->kolam_id,
                'nama_kolam' => $siklus->kolam->nama_kolam,
                'tanggal_mulai' => $waktuMulai,
                'status_aktif' => $siklus->status_aktif,
                'tebar_awal' => $siklus->jumlah_tebar_awal,
                'total_mati' => (int) $totalMati,
                'populasi_panen' => max(0, $siklus->jumlah_tebar_awal - $totalMati),
                'jumlah_ikan_panen' => $panen ? (int) $panen->jumlah_ikan_panen : null,
                'survival_rate' => $panen ? (float) $panen->survival_rate : null,
                'tanggal_panen' => $panen ? $panen->tanggal_panen : null,
            ];
        });

        return Inertia::render('HarvestLog/Index', [
            'dataSiklus' => $dataSiklus,
        ]);
    }

    public function create($siklus_id)
    {
        $siklus = SiklusBudidaya::with('kolam')->findOrFail($siklus_id);

        if ($siklus->status_aktif === 'selesai') {
            return redirect()->route('panen.index');
        }

        $totalMati = MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati');
        $populasiPanen = max(0, $siklus->jumlah_tebar_awal - $totalMati);
        $sr = $siklus->jumlah_tebar_awal > 0
            ? round(($populasiPanen / $siklus->jumlah_tebar_awal) * 100, 2)
            : 0;

        return Inertia::render('HarvestLog/Create', [
            'siklus' => array_merge($siklus->toArray(), [
                'total_mati' => (int) $totalMati,
                'populasi_panen' => $populasiPanen,
                'sr' => $sr,
            ]),
        ]);
    }

    public function store(Request $request, $siklus_id)
    {
        $siklus = SiklusBudidaya::findOrFail($siklus_id);

        if ($siklus->status_aktif === 'selesai') {
            return redirect()->route('panen.index');
        }

        $totalMati = MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati');
        $populasiPanen = max(0, $siklus->jumlah_tebar_awal - $totalMati);
        $sr = $siklus->jumlah_tebar_awal > 0
            ? round(($populasiPanen / $siklus->jumlah_tebar_awal) * 100, 2)
            : 0;

        $validated = $request->validate([
            'tanggal_panen' => 'required|date|after_or_equal:'.$siklus->tanggal_mulai,
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            HarvestLog::create([
                'kolam_id' => $siklus->kolam_id,
                'siklus_budidaya_id' => $siklus->id,
                'user_id' => Auth::id(),
                'jenis_panen' => 'total',
                'tanggal_panen' => $validated['tanggal_panen'],
                'jumlah_ikan_panen' => $populasiPanen,
                'berat_total_kg' => 0,
                'survival_rate' => $sr,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $siklus->update([
                'status_aktif' => 'selesai',
                'tanggal_selesai' => $validated['tanggal_panen'],
            ]);

            $siklus->kolam()->update([
                'jumlah_ikan' => 0,
                'berat_rata_gram' => null,
            ]);

            DB::commit();

            return redirect()->route('panen.index')->with('success', 'Panen berhasil dicatat. Survival rate: '.$sr.'%');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Terjadi kesalahan saat mencatat panen.']);
        }
    }
}
