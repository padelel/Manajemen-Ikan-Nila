<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\TransferLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    /**
     * Menampilkan riwayat pemindahan ikan (Halaman Index).
     */
    public function index()
    {
        // Mengambil data transfer log beserta relasi kolam dan user
        $logs = TransferLog::with(['kolamAsal', 'kolamTujuan', 'user'])
            ->orderBy('tanggal_transfer', 'desc')
            ->get();

        return Inertia::render('Transfer/Index', [
            'logs' => $logs
        ]);
    }

    /**
     * Menampilkan form untuk memindahkan ikan (Halaman Create).
     */
    public function create()
    {
        // Ambil semua kolam yang memiliki ikan (> 0) untuk pilihan kolam asal
        // Dan semua kolam untuk pilihan tujuan
        $kolams = Kolam::all();
        
        return Inertia::render('Transfer/Create', [
            'kolams' => $kolams
        ]);
    }

    /**
     * Menyimpan data pemindahan ke database dan memperbarui populasi kolam.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'kolam_asal_id'   => 'required|exists:kolams,id',
            'kolam_tujuan_id' => 'required|exists:kolams,id|different:kolam_asal_id',
            'jumlah_ikan'     => 'required|integer|min:1',
            'tanggal_transfer'=> 'required|date',
            'catatan'         => 'nullable|string|max:255',
        ], [
            'kolam_tujuan_id.different' => 'Kolam tujuan tidak boleh sama dengan kolam asal.',
            'jumlah_ikan.min'           => 'Minimal jumlah ikan yang dipindah adalah 1 ekor.'
        ]);

        // 2. Mulai Transaksi Database
        DB::beginTransaction();

        try {
            $kolamAsal = Kolam::findOrFail($validated['kolam_asal_id']);
            $kolamTujuan = Kolam::findOrFail($validated['kolam_tujuan_id']);

            // Validasi Ketersediaan: Cek apakah stok di kolam asal mencukupi
            if ($validated['jumlah_ikan'] > $kolamAsal->jumlah_ikan) {
                return back()->withErrors([
                    'jumlah_ikan' => 'Jumlah ikan melebihi total populasi di ' . $kolamAsal->nama_kolam . ' (' . $kolamAsal->jumlah_ikan . ' ekor).'
                ])->withInput();
            }

            // 3. Eksekusi Perubahan Data Kolam
            // Kurangi populasi di kolam asal
            $kolamAsal->decrement('jumlah_ikan', $validated['jumlah_ikan']);
            
            // Tambah populasi di kolam tujuan
            $kolamTujuan->increment('jumlah_ikan', $validated['jumlah_ikan']);

            // 4. Catat ke dalam TransferLog (Histori)
            TransferLog::create([
                'kolam_asal_id'   => $validated['kolam_asal_id'],
                'kolam_tujuan_id' => $validated['kolam_tujuan_id'],
                'user_id'         => Auth::id(),
                'tanggal_transfer'=> $validated['tanggal_transfer'],
                'jumlah_ikan'     => $validated['jumlah_ikan'],
                'catatan'         => $validated['catatan'] ?? 'Pemindahan rutin antar kolam.',
            ]);

            // Jika semua lancar, simpan permanen
            DB::commit();

            return redirect()->route('transfer.index')->with('success', 'Berhasil memindahkan ' . $validated['jumlah_ikan'] . ' ekor ikan.');

        } catch (\Exception $e) {
            // Jika ada error (listrik mati, DB error, dll), batalkan semua perubahan
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Gagal memproses pemindahan: ' . $e->getMessage()
            ])->withInput();
        }
    }
}