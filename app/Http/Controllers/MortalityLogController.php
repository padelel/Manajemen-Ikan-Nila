<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MortalityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = MortalityLog::with(['kolam', 'siklusBudidaya', 'user'])->latest('tanggal_kematian');

        if ($request->filled('kolam_id')) {
            $query->where('kolam_id', $request->kolam_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_kematian', $request->kategori);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_kematian', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_kematian', '<=', $request->end_date);
        }

        $logs = $query->paginate(10)->withQueryString();

        return Inertia::render('Mortality/Index', [
            'logs' => $logs,
            'kolams' => Kolam::all(),
            'filters' => $request->only(['kolam_id', 'kategori', 'start_date', 'end_date']),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // Hanya tampilkan kolam yang memiliki siklus berjalan
        $kolamQuery = Kolam::whereIn('id', SiklusBudidaya::where('status_aktif', 'berjalan')->pluck('kolam_id'));

        if ($user->role === 'operator') {
            $kolamIds = $user->kolams()->pluck('kolams.id');
            $kolamQuery->whereIn('id', $kolamIds);
        }

        $kolams = $kolamQuery->get()->map(function ($kolam) {
            $siklus = SiklusBudidaya::where('kolam_id', $kolam->id)
                ->where('status_aktif', 'berjalan')
                ->latest('tanggal_mulai')
                ->first();

            $totalMati = $siklus
                ? MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati')
                : 0;

            $populasiTerkini = $siklus
                ? max(0, $siklus->jumlah_tebar_awal - $totalMati)
                : 0;

            $sr = $siklus && $siklus->jumlah_tebar_awal > 0
                ? round(($populasiTerkini / $siklus->jumlah_tebar_awal) * 100, 1)
                : null;

            return [
                'id' => $kolam->id,
                'nama_kolam' => $kolam->nama_kolam,
                'lokasi' => $kolam->lokasi,
                'jumlah_ikan' => $populasiTerkini,
                'siklus_id' => $siklus?->id,
                'siklus_mulai' => $siklus?->tanggal_mulai,
                'tebar_awal' => $siklus?->jumlah_tebar_awal,
                'total_mati_siklus' => $totalMati,
                'sr' => $sr,
            ];
        });

        return Inertia::render('Mortality/Create', [
            'kolams' => $kolams,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'siklus_budidaya_id' => 'required|exists:siklus_budidayas,id',
            'tanggal_kematian' => 'required|date',
            'jumlah_mati' => 'required|integer|min:1',
            'kategori_kematian' => 'nullable|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        // Validasi bahwa siklus memang milik kolam tersebut
        $siklus = SiklusBudidaya::where('id', $validated['siklus_budidaya_id'])
            ->where('kolam_id', $validated['kolam_id'])
            ->where('status_aktif', 'berjalan')
            ->first();

        if (! $siklus) {
            return back()->withErrors(['siklus_budidaya_id' => 'Siklus tidak valid atau sudah selesai.'])->withInput();
        }

        // Validasi jumlah mati tidak melebihi populasi terkini
        $totalMatiSebelumnya = MortalityLog::where('siklus_budidaya_id', $siklus->id)->sum('jumlah_mati');
        $populasiTerkini = $siklus->jumlah_tebar_awal - $totalMatiSebelumnya;

        if ($validated['jumlah_mati'] > $populasiTerkini) {
            return back()->withErrors(['jumlah_mati' => "Jumlah mati tidak boleh melebihi populasi tersisa ({$populasiTerkini} ekor)."])->withInput();
        }

        DB::transaction(function () use ($validated, $siklus) {
            MortalityLog::create([
                'kolam_id' => $validated['kolam_id'],
                'siklus_budidaya_id' => $validated['siklus_budidaya_id'],
                'user_id' => Auth::id(),
                'tanggal_kematian' => $validated['tanggal_kematian'],
                'jumlah_mati' => $validated['jumlah_mati'],
                'kategori_kematian' => $validated['kategori_kematian'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Update denormalized jumlah_ikan di kolam
            $siklus->kolam()->decrement('jumlah_ikan', $validated['jumlah_mati']);
        });

        return redirect()->route('kematian.index')->with('success', 'Data kematian berhasil dicatat.');
    }
}
