<?php

namespace App\Http\Controllers;

use App\Models\MortalityLog;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MortalityLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar - Gunakan 'tanggal_kematian' bukan 'tanggal'
        $query = MortalityLog::with(['kolam', 'user'])->latest('tanggal_kematian');

        // 2. Filter Kolam
        if ($request->filled('kolam_id')) {
            $query->where('kolam_id', $request->kolam_id);
        }

        // 3. Filter Tanggal Mulai
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_kematian', '>=', $request->start_date);
        }

        // 4. Filter Tanggal Akhir
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_kematian', '<=', $request->end_date);
        }

        // 5. Pagination
        $logs = $query->paginate(10)->withQueryString();

        return Inertia::render('Mortality/Index', [
            'logs' => $logs,
            'kolams' => Kolam::all(), // Untuk dropdown filter kolam
            'filters' => $request->only(['kolam_id', 'start_date', 'end_date'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Mortality/Create', [
            'kolams' => Kolam::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'tanggal_kematian' => 'required|date',
            'jumlah_mati' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        // 1. Simpan data kematian ke tabel mortality_logs
        MortalityLog::create($validated);

        // 2. Kurangi total ikan di tabel kolams secara otomatis
        $kolam = Kolam::find($request->kolam_id);
        $kolam->jumlah_ikan -= $request->jumlah_mati;
        $kolam->save();

        return redirect()->route('kematian.index');
    }
}