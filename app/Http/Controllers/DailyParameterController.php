<?php

namespace App\Http\Controllers;

use App\Models\DailyParameter;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DailyParameterController extends Controller
{
    public function index()
    {
        // Mengambil data parameter beserta nama kolam dan nama usernya
        $parameters = DailyParameter::with(['kolam', 'user'])->latest()->get();
        
        return Inertia::render('Parameter/Index', [
            'parameters' => $parameters
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