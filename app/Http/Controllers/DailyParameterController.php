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
            'kondisi_visual' => 'required|in:Jernih,Keruh,Berbusa',
        ]);

        // Otomatis mencatat ID user yang sedang login
        $validated['user_id'] = Auth::id();

        DailyParameter::create($validated);
        return redirect()->route('parameter.index');
    }
}