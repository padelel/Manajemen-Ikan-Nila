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
        // Jika bundar, paksa nilai lebar sama dengan panjang/diameter
        if ($request->bentuk_kolam === 'Bundar') {
            $request->merge(['lebar_m' => $request->panjang_m]);
        }

        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'bentuk_kolam' => 'required|in:Bundar,Persegi',
            'panjang_m' => 'required|numeric',
            'lebar_m' => 'required|numeric',
            'kedalaman_m' => 'required|numeric',
            'tanggal_tebar' => 'required|date',
            'jumlah_ikan' => 'required|integer',
            'berat_rata_gram' => 'required|numeric',
        ]);

        $validated['status_kolam'] = 'Aktif'; // Default status
        Kolam::create($validated);

        return redirect()->route('kolam.index')->with('message', 'Kolam berhasil ditambahkan!');
    }

    public function edit(Kolam $kolam)
    {
        // Mengirim data 1 kolam spesifik ke halaman Edit.vue
        return Inertia::render('Kolam/Edit', [
            'kolam' => $kolam
        ]);
    }

    public function update(Request $request, $id)
    {
        $kolam = Kolam::findOrFail($id);

        // 1. Logika Penyesuaian Dimensi (Otomatis samakan lebar dengan panjang jika Bundar)
        if ($request->bentuk_kolam === 'Bundar') {
            $request->merge(['lebar_m' => $request->panjang_m]);
        }

        // 2. Validasi struktur kolom yang baru
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'bentuk_kolam' => 'required|in:Bundar,Persegi',
            'panjang_m' => 'required|numeric',
            'lebar_m' => 'required|numeric',
            'kedalaman_m' => 'required|numeric',
            'tanggal_tebar' => 'required|date',
            'jumlah_ikan' => 'required|integer',
            'berat_rata_gram' => 'required|numeric',
        ]);

        // 3. Update data (Tidak perlu mengubah status_kolam agar tidak mereset kolam yang sedang 'Kosong'/'Panen')
        $kolam->update($validated);

        return redirect()->route('kolam.index')->with('message', 'Data Kolam berhasil diperbarui!');
    }

    public function destroy(Kolam $kolam)
    {
        $kolam->delete();
        return redirect()->route('kolam.index');
    }
}