<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use Inertia\Inertia; // Wajib dipanggil untuk merender Vue
use Illuminate\Http\Request;

class KolamController extends Controller
{
    public function index()
    {
        // Mengambil semua data dari tabel kolams
        $kolams = Kolam::all(); 
        
        // Mengirim data ke file Vue yang bernama 'Kolam/Index'
        return Inertia::render('Kolam/Index', [
            'kolams' => $kolams
        ]);
    }

    public function create()
    {
        return Inertia::render('Kolam/Create');
    }

    public function store(Request $request)
    {
        // 1. Validasi input agar tidak ada data kosong atau salah format
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'dimensi' => 'required|string|max:255',
            'jumlah_ikan' => 'required|integer|min:0',
            'berat_rata_gram' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        // 2. Simpan ke database
        Kolam::create($validated);

        // 3. Alihkan kembali ke halaman utama tabel
        return redirect()->route('kolam.index');
    }

    public function edit(Kolam $kolam)
    {
        // Mengirim data 1 kolam spesifik ke halaman Edit.vue
        return Inertia::render('Kolam/Edit', [
            'kolam' => $kolam
        ]);
    }

    public function update(Request $request, Kolam $kolam)
    {
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'dimensi' => 'required|string|max:255',
            'jumlah_ikan' => 'required|integer|min:0',
            'berat_rata_gram' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        $kolam->update($validated);
        return redirect()->route('kolam.index');
    }

    public function destroy(Kolam $kolam)
    {
        $kolam->delete();
        return redirect()->route('kolam.index');
    }
}