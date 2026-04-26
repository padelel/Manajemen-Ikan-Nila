<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\Rule;
use App\Models\MortalityLog;
use App\Models\DailyParameter;
use App\Models\FeedLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DailyOperationController extends Controller
{
    public function create()
    {
        return Inertia::render('DailyOperation/Create', [
            'kolams' => Kolam::all(),
            'inventories' => Inventory::where('total_stok_kg', '>', 0)->get(),
            'rules' => Rule::all(), 
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Super Ketat (Menambahkan frekuensi)
        $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jumlah_mati' => 'required|integer|min:0',
            'suhu' => 'required|numeric',
            'ph' => 'required|numeric',
            'kondisi_visual' => 'required|string',
            'berat_sample' => 'required|numeric|min:0.1',
            'frekuensi' => 'required|integer|min:1', // <--- PERBAIKAN 1: Validasi frekuensi
            'rule_id' => 'required|exists:rules,id',
            'rekomendasi_sistem' => 'required|numeric',
            'pakan_aktual' => 'required|numeric|min:0',
            'feeds' => 'required|array|min:1',
        ]);

        // 2. Database Transaction (Aman dari kegagalan sistem)
        DB::beginTransaction();
        try {
            $kolam = Kolam::findOrFail($request->kolam_id);
            $userId = Auth::id();
            $tanggal = Carbon::now();

            // STEP 1: Simpan Mortalitas (Jika ada yang mati)
            if ($request->jumlah_mati > 0) {
                MortalityLog::create([
                    'kolam_id' => $kolam->id,
                    'user_id' => $userId,
                    'tanggal' => $tanggal, 
                    'jumlah' => $request->jumlah_mati, 
                    'catatan' => $request->catatan_kematian ?? 'Kematian harian'
                ]);
                $kolam->decrement('jumlah_ikan', $request->jumlah_mati);
            }

            // STEP 2: Simpan Kualitas Air & Sample
            DailyParameter::create([
                'kolam_id' => $kolam->id,
                'user_id' => $userId,
                'tanggal_cek' => $tanggal,
                'suhu' => $request->suhu,
                'ph' => $request->ph,
                'kondisi_visual' => $request->kondisi_visual,
                'berat_sample' => $request->berat_sample,
            ]);
            $kolam->update(['berat_rata_gram' => $request->berat_sample]);

            // STEP 3: Simpan Log Pakan Induk (Termasuk Frekuensi)
            $feedLog = FeedLog::create([
                'kolam_id' => $kolam->id,
                'rule_id' => $request->rule_id,
                'user_id' => $userId,
                'tanggal_pakan' => $tanggal,
                'frekuensi' => $request->frekuensi, // <--- PERBAIKAN 2: Simpan frekuensi ke database
                'rekomendasi_sistem' => $request->rekomendasi_sistem,
                'pakan_aktual' => $request->pakan_aktual,
            ]);

            // STEP 4: Simpan Detail Multi-Pakan & Potong Stok
            $totalRasio = collect($request->feeds)->sum('rasio');
            foreach ($request->feeds as $feed) {
                $jumlahKg = ($feed['rasio'] / $totalRasio) * $request->pakan_aktual;
                
                $feedLog->details()->create([
                    'inventory_id' => $feed['inventory_id'],
                    'rasio' => $feed['rasio'],
                    'jumlah_kg' => round($jumlahKg, 2),
                ]);

                // Potong stok inventory
                $inventory = Inventory::find($feed['inventory_id']);
                $inventory->update([
                    'total_stok_kg' => round($inventory->total_stok_kg - $jumlahKg, 2)
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard')->with('message', 'Operasi Harian Terpadu Berhasil Disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
}