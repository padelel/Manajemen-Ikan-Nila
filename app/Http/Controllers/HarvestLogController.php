<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\SiklusBudidaya; // Tambahkan ini agar lebih rapi
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HarvestLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai query dasar (tarik relasi kolam dan user)
        $query = HarvestLog::with(['kolam', 'user']);

        // 2. Terapkan Filter: Jenis Panen (Parsial / Full)
        if ($request->filled('jenis_panen') && $request->jenis_panen !== 'Semua') {
            $query->where('jenis_panen', $request->jenis_panen);
        }

        // 3. Terapkan Filter: Tanggal Mulai
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_panen', '>=', $request->tanggal_mulai);
        }

        // 4. Terapkan Filter: Tanggal Akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_panen', '<=', $request->tanggal_akhir);
        }

        // 5. Eksekusi query (ambil data yang sudah difilter, urutkan dari yang terbaru)
        $logs = $query->orderBy('tanggal_panen', 'desc')->get();

        // 6. Kirim data ke Vue beserta input filter (agar kotak pencarian tetap terisi setelah halaman refresh)
        return Inertia::render('HarvestLog/Index', [
            'logs' => $logs,
            'filters' => $request->only(['jenis_panen', 'tanggal_mulai', 'tanggal_akhir'])
        ]);
    }

    public function create()
    {
        $kolams = Kolam::where('jumlah_ikan', '>', 0)->get(); // Hanya tampilkan kolam yang masih ada ikannya
        return Inertia::render('HarvestLog/Create', ['kolams' => $kolams]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jenis_panen' => 'required|in:Parsial,Full', // <-- Disini pakainya 'Full'
            'tanggal_panen' => 'required|date',
            'jumlah_ikan' => 'required|integer|min:1',
            'berat_total_kg' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $kolam = Kolam::findOrFail($request->kolam_id);

            // Validasi agar jumlah panen tidak melebihi sisa ikan di kolam
            if ($validated['jumlah_ikan'] > $kolam->jumlah_ikan) {
                return back()->withErrors(['jumlah_ikan' => 'Jumlah ikan dipanen melebihi populasi kolam saat ini (Sisa: ' . $kolam->jumlah_ikan . ' Ekor).']);
            }

            // Kurangi populasi ikan di kolam
            if ($validated['jenis_panen'] === 'Full') {
                $kolam->jumlah_ikan = 0; // Jika panen full, kosongkan kolam
            } else {
                $kolam->jumlah_ikan -= $validated['jumlah_ikan']; // Jika parsial, kurangi sebagian
            }
            $kolam->save();

            // Simpan riwayat panen
            $validated['user_id'] = Auth::id();
            HarvestLog::create($validated);

            // =========================================================
            // PERBAIKAN: Gunakan 'Full', bukan 'Total'
            // =========================================================
            if ($validated['jenis_panen'] === 'Full') {
                $siklusAktif = SiklusBudidaya::where('kolam_id', $request->kolam_id)
                                ->where('status_aktif', 'Aktif')
                                ->first();
                                
                if ($siklusAktif) {
                    $siklusAktif->update([
                        'status_aktif' => 'Selesai',
                        'tanggal_selesai' => $request->tanggal_panen
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('panen.index')->with('success', 'Data panen berhasil dicatat dan populasi kolam telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}