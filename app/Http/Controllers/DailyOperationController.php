<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\Inventory;
use App\Models\Rule;
use App\Models\MortalityLog;
use App\Models\DailyParameter;
use App\Models\FeedLog;
use App\Models\InventoryLog;
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
        // 1. Validasi Input (Termasuk Frekuensi & Detail Pakan)
        $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'jumlah_mati' => 'required|integer|min:0',
            'suhu' => 'required|numeric',
            'ph' => 'required|numeric',
            'kondisi_visual' => 'required|string',
            'berat_sample' => 'required|numeric|min:0.1',
            'frekuensi' => 'required|integer|min:1',
            'rule_id' => 'required|exists:rules,id',
            'rekomendasi_sistem' => 'required|numeric',
            'pakan_aktual' => 'required|numeric|min:0',
            'feeds' => 'required|array|min:1',
        ]);

        // 2. Database Transaction (Aman dari kegagalan sistem sebagian)
        DB::beginTransaction();
        try {
            $kolam = Kolam::findOrFail($request->kolam_id);
            $userId = Auth::id();
            $tanggal = Carbon::now();

            // STEP 1: Simpan Mortalitas (Jika ada laporan kematian ikan)
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

            // STEP 2: Simpan Kualitas Air & Sample Berat Rata-rata
            DailyParameter::create([
                'kolam_id' => $kolam->id,
                'user_id' => $userId,
                'tanggal_cek' => $tanggal,
                'suhu' => $request->suhu,
                'ph' => $request->ph,
                'kondisi_visual' => $request->kondisi_visual,
                'berat_sample' => $request->berat_sample,
            ]);
            // Update berat rata-rata kolam terbaru
            $kolam->update(['berat_rata_gram' => $request->berat_sample]);

            // STEP 3: Simpan Log Pakan Induk
            $feedLog = FeedLog::create([
                'kolam_id' => $kolam->id,
                'rule_id' => $request->rule_id,
                'user_id' => $userId,
                'tanggal_pakan' => $tanggal,
                'frekuensi' => $request->frekuensi,
                'rekomendasi_sistem' => $request->rekomendasi_sistem,
                'pakan_aktual' => $request->pakan_aktual,
            ]);

            // STEP 4: Simpan Detail Multi-Pakan, Potong Stok Gudang, & Catat Riwayat
            $totalRasio = collect($request->feeds)->sum('rasio');
            foreach ($request->feeds as $feed) {
                // Hitung berat riil per jenis pakan berdasarkan rasio campuran
                $jumlahKg = ($feed['rasio'] / $totalRasio) * $request->pakan_aktual;
                $jumlahKgBulat = round($jumlahKg, 2);
                
                if ($jumlahKgBulat > 0) {
                    // 4a. Catat rincian pakan yang terpakai ke detail tabel FeedLog
                    $feedLog->details()->create([
                        'inventory_id' => $feed['inventory_id'],
                        'rasio' => $feed['rasio'],
                        'jumlah_kg' => $jumlahKgBulat,
                    ]);

                    // 4b. Potong stok master di tabel Gudang (Inventory)
                    $inventory = Inventory::find($feed['inventory_id']);
                    $inventory->update([
                        'total_stok_kg' => round($inventory->total_stok_kg - $jumlahKgBulat, 2)
                    ]);

                    // 4c. Catat histori pengeluaran ke tabel InventoryLog (Riwayat Gudang Admin)
                    InventoryLog::create([
                        'inventory_id' => $feed['inventory_id'], 
                        'user_id'      => $userId, 
                        'tipe'         => 'Keluar',
                        'jumlah'       => $jumlahKgBulat, 
                        'keterangan'   => 'Pemberian pakan harian untuk ' . $kolam->nama_kolam,
                    ]);
                }
            }

            // Kunci Transaksi agar semua aksi di atas tersimpan ke database
            DB::commit();
            return redirect()->route('feedlog.index')->with('message', 'Operasi Harian Terpadu Berhasil Disimpan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua aksi jika ada 1 saja yang error
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
}