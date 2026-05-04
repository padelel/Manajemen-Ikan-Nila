<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Kolam;
use App\Models\KolamLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KolamController extends Controller
{
    public function index()
    {
        return Inertia::render('Kolam/Index', [
            'kolams' => Kolam::all(),
            // Mengambil 10 log terakhir beserta data user yang mengeksekusinya
            'logs' => KolamLog::with('user')->latest()->take(10)->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Kolam/Create');
    }

    public function store(Request $request)
    {
        // 1. Validasi HANYA untuk fisik & status kolam
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'bentuk_kolam' => 'required|string|max:50',
            'status_kolam' => 'required|string|max:50',
            'panjang_m' => 'required|numeric',
            'lebar_m' => 'nullable|numeric',
            'kedalaman_m' => 'required|numeric',
        ]);

        // 2. Set default populasi & berat = 0 (Karena baru daftar fisik kolam)
        $validated['jumlah_ikan'] = 0;
        $validated['berat_rata_gram'] = 0;

        $kolam = Kolam::create($validated);

        // 3. CATAT LOG: TAMBAH
        KolamLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'Tambah',
            'nama_kolam' => $kolam->nama_kolam,
            'keterangan' => 'Menambahkan data kolam fisik baru ke dalam sistem.'
        ]);

        return redirect()->route('kolam.index')->with('success', 'Kolam fisik berhasil didaftarkan.');
    }

    public function edit(Kolam $kolam)
    {
        return Inertia::render('Kolam/Edit', [
            'kolam' => $kolam
        ]);
    }

    public function update(Request $request, Kolam $kolam)
    {
        // 1. Validasi HANYA untuk fisik & status kolam
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'bentuk_kolam' => 'required|string|max:50',
            'status_kolam' => 'required|string|max:50',
            'panjang_m' => 'required|numeric',
            'lebar_m' => 'nullable|numeric',
            'kedalaman_m' => 'required|numeric',
        ]);

        // Catatan: jumlah_ikan & berat_rata_gram tidak di-update di sini 
        // agar populasi ikan tidak ter-reset saat mengedit status/dimensi kolam.
        $kolam->update($validated);

        // 2. CATAT LOG: UPDATE
        KolamLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'Update',
            'nama_kolam' => $kolam->nama_kolam,
            'keterangan' => 'Memperbarui spesifikasi fisik / status kolam.'
        ]);

        return redirect()->route('kolam.index')->with('success', 'Data kolam berhasil diperbarui.');
    }

    public function destroy(Kolam $kolam)
    {
        $namaKolam = $kolam->nama_kolam; // Simpan namanya dulu sebelum record-nya hilang
        
        $kolam->delete();

        // CATAT LOG: HAPUS
        KolamLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'Hapus',
            'nama_kolam' => $namaKolam,
            'keterangan' => 'Menghapus data kolam dari database secara permanen.'
        ]);

        return redirect()->route('kolam.index')->with('success', 'Kolam berhasil dihapus.');
    }
}