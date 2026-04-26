<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HarvestLogController extends Controller
{
    public function index()
    {
        $logs = HarvestLog::with(['kolam', 'user'])->orderBy('tanggal_panen', 'desc')->get();
        return Inertia::render('HarvestLog/Index', ['logs' => $logs]);
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
            'jenis_panen' => 'required|in:Parsial,Full',
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

            DB::commit();
            return redirect()->route('panen.index')->with('success', 'Data panen berhasil dicatat dan populasi kolam telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem.']);
        }
    }
}