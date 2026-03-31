<?php

namespace App\Http\Controllers;

use App\Models\MortalityLog;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MortalityLogController extends Controller
{
    public function index()
    {
        $logs = MortalityLog::with(['kolam', 'user'])->latest()->get();
        return Inertia::render('Mortality/Index', ['logs' => $logs]);
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