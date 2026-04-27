<?php

namespace App\Http\Controllers;

use App\Models\DailyParameter;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DailyParameterController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar
        $query = DailyParameter::with(['kolam', 'user'])->latest('tanggal_cek');

        // 2. Filter berdasarkan Kolam
        if ($request->filled('kolam_id')) {
            $query->where('kolam_id', $request->kolam_id);
        }

        // 3. Filter berdasarkan Tanggal Mulai
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_cek', '>=', $request->start_date);
        }

        // 4. Filter berdasarkan Tanggal Akhir
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_cek', '<=', $request->end_date);
        }

        // 5. Pagination
        $parameters = $query->paginate(10)->withQueryString();

        return Inertia::render('Parameter/Index', [
            'parameters' => $parameters,
            'kolams' => Kolam::all(), // Untuk dropdown filter
            'filters' => $request->only(['kolam_id', 'start_date', 'end_date'])
        ]);
    }

    public function create()
    {
        // Mengirim daftar kolam untuk dipilih di dropdown form
        return Inertia::render('Parameter/Create', [
            'kolams' => Kolam::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'tanggal_cek' => 'required|date',
            'suhu' => 'required|numeric',
            'ph' => 'required|numeric',
            'kondisi_visual' => 'required|string',
            'berat_sample' => 'required|numeric|min:0', 
        ]);

        // Jika tabel daily_parameters Anda memiliki kolom user_id, tambahkan baris ini:
        $validated['user_id'] = Auth::id(); 

        // 1. Simpan Pengecekan (GANTI Parameter MENJADI DailyParameter)
        DailyParameter::create($validated);

        // 2. Auto-Update berat_rata_gram di tabel kolams
        $kolam = Kolam::find($request->kolam_id);
        if ($kolam) {
            $kolam->update([
                'berat_rata_gram' => $request->berat_sample
            ]);
        }

        return redirect()->route('parameter.index')->with('message', 'Data berhasil disimpan!');
    }
}