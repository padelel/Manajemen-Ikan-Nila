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
        // 1. Validasi input
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jenis_panen' => 'required|in:Parsial,Full',
            'tanggal_panen' => 'required|date',
            'jumlah_ikan' => 'required|integer|min:1',
            'berat_total_kg' => 'required|numeric|min:0.1',
            'catatan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Ambil data kolam yang akan dipanen
            $kolam = Kolam::findOrFail($validated['kolam_id']);

            // Validasi: Pastikan jumlah panen tidak melebihi stok yang ada di kolam
            if ($validated['jumlah_ikan'] > $kolam->jumlah_ikan) {
                return back()->withErrors([
                    'jumlah_ikan' => 'Jumlah ikan yang dipanen ('. $validated['jumlah_ikan'] .') melebihi total populasi kolam saat ini ('. $kolam->jumlah_ikan .').'
                ])->withInput();
            }

            // ====================================================================
            // LOGIKA AUTO-CORRECTION: JIKA PARSIAL TAPI MENGHABISKAN SEMUA IKAN, 
            // MAKA OTOMATIS UBAH JADI PANEN FULL
            // ====================================================================
            if ($validated['jenis_panen'] === 'Parsial' && $validated['jumlah_ikan'] == $kolam->jumlah_ikan) {
                $validated['jenis_panen'] = 'Full';
                $validated['catatan'] = ($validated['catatan'] ?? '') . ' (Sistem Otomatis: Diubah menjadi panen Full karena memanen seluruh sisa populasi kolam).';
            }

            // Simpan Log Panen
            $validated['user_id'] = Auth::id();
            HarvestLog::create($validated);

            // ====================================================================
            // PROSES PENGURANGAN POPULASI DAN PENUTUPAN SIKLUS
            // ====================================================================
            if ($validated['jenis_panen'] === 'Full') {
                // Jika Panen Full (atau sudah dikoreksi jadi Full): Kosongkan kolam
                $kolam->update([
                    'jumlah_ikan' => 0,
                    'berat_rata_gram' => 0,
                ]);

                // Tutup Siklus Budidaya yang sedang berjalan di kolam ini
                $siklusAktif = \App\Models\SiklusBudidaya::where('kolam_id', $kolam->id)
                                ->where('status_aktif', 'Aktif')
                                ->first();
                if ($siklusAktif) {
                    $siklusAktif->update([
                        'tanggal_selesai' => $validated['tanggal_panen'],
                        'status_aktif' => 'Selesai'
                    ]);
                }

            } else {
                // Jika Panen Parsial (dan belum habis): Kurangi populasi saja
                $kolam->decrement('jumlah_ikan', $validated['jumlah_ikan']);
            }

            DB::commit();

            return redirect()->route('panen.index')->with('success', 'Data panen berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])->withInput();
        }
    }
}