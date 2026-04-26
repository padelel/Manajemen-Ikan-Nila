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
        // Mengambil semua data tebar log beserta relasi kolam dan user, diurutkan dari yang terbaru
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
        // Mengambil semua data kolam untuk ditampilkan di dropdown pilihan kolam
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

        // 2. Gunakan Database Transaction agar aman (Jika salah satu gagal, semua dibatalkan)
        DB::beginTransaction();
        
        try {
            // A. Simpan log riwayat tebar benih
            $validated['user_id'] = Auth::id(); // Tambahkan ID user yang sedang login
            TebarLog::create($validated);

            // B. Update jumlah populasi di tabel Kolam (Ditambah/Increment)
            $kolam = Kolam::findOrFail($validated['kolam_id']);
            $kolam->increment('jumlah_ikan', $validated['jumlah_ikan']);
            
            // C. Opsional: Anda juga bisa mengupdate berat_rata_gram di tabel kolam 
            // jika tebar benih baru ini secara signifikan mengubah rata-rata keseluruhan.
            // Namun untuk tahap awal, increment jumlah_ikan saja sudah cukup.

            DB::commit(); // Simpan permanen

            // Kembali ke halaman index dengan pesan sukses
            return redirect()->route('tebar.index')->with('success', 'Data tebar benih berhasil dicatat dan populasi kolam bertambah.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan database jika terjadi error
            
            // Kembali ke form dengan membawa pesan error
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])->withInput();
        }
    }
}