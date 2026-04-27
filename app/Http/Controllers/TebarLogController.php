<?php

namespace App\Http\Controllers;

use App\Models\TebarLog;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TebarLogController extends Controller
{
    /**
     * Menampilkan daftar riwayat tebar benih (Halaman Index)
     */
    public function index()
    {
        $logs = TebarLog::with(['kolam', 'user'])->orderBy('tanggal_tebar', 'desc')->get();
        
        return Inertia::render('TebarLog/Index', [
            'logs' => $logs
        ]);
    }

    /**
     * Menampilkan form untuk mencatat tebar benih baru (Halaman Create)
     */
    public function create()
    {
        $kolams = Kolam::all();
        
        return Inertia::render('TebarLog/Create', [
            'kolams' => $kolams
        ]);
    }

    /**
     * Menyimpan data tebar benih baru ke database
     */
    public function store(Request $request)
    {
        // 1. Validasi input dari form
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'tanggal_tebar' => 'required|date',
            'jumlah_ikan' => 'required|integer|min:1',
            'berat_rata_gram' => 'required|numeric|min:0.1',
            'sumber_benih' => 'nullable|string|max:255',
            'catatan' => 'nullable|string'
        ]);

        // 2. Gunakan Database Transaction agar aman
        DB::beginTransaction();
        
        try {
            // A. Simpan log riwayat tebar benih
            $validated['user_id'] = Auth::id(); 
            TebarLog::create($validated);

            // B. Cek apakah kolam sudah punya siklus aktif
            $siklusAktif = \App\Models\SiklusBudidaya::where('kolam_id', $validated['kolam_id'])
                            ->where('status_aktif', 'Aktif')
                            ->first();

            if (!$siklusAktif) {
                // Buka Siklus Baru (PERBAIKAN: Gunakan $validated['jumlah_ikan'])
                \App\Models\SiklusBudidaya::create([
                    'kolam_id' => $validated['kolam_id'],
                    'tanggal_mulai' => $validated['tanggal_tebar'],
                    'status_aktif' => 'Aktif',
                    'jumlah_tebar_awal' => $validated['jumlah_ikan'] 
                ]);
            } else {
                // Tebar susulan (Parsial), tambah jumlah bibit saja (PERBAIKAN: Gunakan $validated['jumlah_ikan'])
                $siklusAktif->increment('jumlah_tebar_awal', $validated['jumlah_ikan']);
            }

            // C. Update jumlah populasi di tabel Kolam
            $kolam = Kolam::findOrFail($validated['kolam_id']);
            $kolam->increment('jumlah_ikan', $validated['jumlah_ikan']);
            
            DB::commit(); // Simpan permanen

            return redirect()->route('tebar.index')->with('success', 'Data tebar benih berhasil dicatat dan populasi kolam bertambah.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika terjadi error
            
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])->withInput();
        }
    }
}