<?php

namespace App\Http\Controllers;

use App\Models\FeedLog;
use App\Models\Kolam;
use App\Models\Rule;
use App\Models\Inventory;
use App\Models\DailyParameter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FeedLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Siapkan Query Dasar
        $query = FeedLog::with(['kolam', 'user', 'feedLogDetails.inventory'])->orderBy('tanggal_pakan', 'desc');

        // 2. Terapkan Filter jika ada request
        if ($request->filled('kolam_id')) {
            $query->where('kolam_id', $request->kolam_id);
        }
        
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_pakan', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_pakan', '<=', $request->end_date);
        }

        // 3. Ambil Hasil
        $logs = $query->get();
        $kolams = Kolam::all(); // Untuk dropdown filter kolam

        // 4. Kirim ke Vue
        return Inertia::render('FeedLog/Index', [
            'logs' => $logs,
            'kolams' => $kolams,
            'filters' => $request->only(['kolam_id', 'start_date', 'end_date']) // Kirim balik filter agar state terjaga
        ]);
    }

    public function create()
    {
        // Mengirim data pendukung ke form Vue
        return Inertia::render('FeedLog/Create', [
            'kolams' => Kolam::all(),
            'inventories' => Inventory::where('total_stok_kg', '>', 0)->get(),
        ]);
    }

    public function hitungRekomendasi($kolam_id)
    {
        $kolam = Kolam::find($kolam_id);
        
        // 1. Cari data air terakhir hari ini/kemarin
        $parameter = DailyParameter::where('kolam_id', $kolam_id)->latest('tanggal_cek')->first();

        // 2. Hitung Biomassa Dasar (Konversi Gram ke Kg)
        $biomassa_kg = ($kolam->jumlah_ikan * $kolam->berat_rata_gram) / 1000;
        
        // Standar pakan harian 5% dari berat tubuh
        $dosis_harian_kg = $biomassa_kg * 0.05; 

        // Karena jadwal makan 2x sehari (Pagi & Sore), dosis harian dibagi 2
        $dosis_dasar_kg = $dosis_harian_kg / 2; 

        if (!$parameter) {
            return response()->json(['error' => 'Data kualitas air belum diinput untuk kolam ini.']);
        }

        // 3. MESIN INFERENSI FORWARD CHAINING
        $suhu = $parameter->suhu;
        $ph = $parameter->ph;
        $visual = $parameter->kondisi_visual;

        // Default: R01 (Normal 100%)
        $rule = Rule::where('kode_rule', 'R01')->first(); 
        $persentase = 100;

        // Rule R03: Kritis (Stop Pakan 0%)
        if ($ph < 6.5 || $ph > 8.5 || $visual == 'Berbusa') {
            $rule = Rule::where('kode_rule', 'R03')->first();
            $persentase = 0;
        } 
        // Rule R02: Waspada (Pakan Dipotong 50%)
        elseif ($suhu < 25 || $suhu > 32 || $visual == 'Keruh') {
            $rule = Rule::where('kode_rule', 'R02')->first();
            $persentase = 50;
        }

        // 4. Perhitungan Dosis Akhir
        $rekomendasi_akhir = $dosis_dasar_kg * ($persentase / 100);

        return response()->json([
            'biomassa_kg' => round($biomassa_kg, 2),
            'dosis_dasar_kg' => round($dosis_dasar_kg, 2),
            'suhu' => $suhu,
            'ph' => $ph,
            'rule_id' => $rule->id,
            'kode_rule' => $rule->kode_rule,
            'rekomendasi_akhir_kg' => round($rekomendasi_akhir, 2)
        ]);
    }

    public function store(Request $request)
    {
        // =========================================================
        // 1. RADAR ERROR VALIDASI
        // =========================================================
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'kolam_id' => 'required|exists:kolams,id',
            'rule_id' => 'required',
            'tanggal_pakan' => 'required|date',
            'frekuensi' => 'nullable|integer', // Boleh kosong, otomatis diisi 2 nanti
            'rekomendasi_sistem' => 'required|numeric',
            'pakan_aktual' => 'required|numeric|min:0.1',
            'feeds' => 'required|array|min:1',
            'feeds.*.inventory_id' => 'required|exists:inventories,id',
            'feeds.*.rasio' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            // Jika layar menjadi hitam dan menampilkan ini, berarti ada input Vue yang salah!
            dd('GAGAL VALIDASI!', $validator->errors()->toArray(), 'DATA DARI VUE:', $request->all());
        }

        // =========================================================
        // 2. PROSES PENYIMPANAN DATABASE
        // =========================================================
        \DB::beginTransaction();
        try {
            $feedLog = FeedLog::create([
                'user_id' => Auth::id(),
                'kolam_id' => $request->kolam_id,
                'rule_id' => $request->rule_id,
                'tanggal_pakan' => $request->tanggal_pakan,
                'frekuensi' => $request->frekuensi ?? 2, // Default 2 kali jika UI tidak mengirim frekuensi
                'rekomendasi_sistem' => $request->rekomendasi_sistem,
                'pakan_aktual' => $request->pakan_aktual,
            ]);

            $totalRasio = collect($request->feeds)->sum('rasio');

            foreach ($request->feeds as $feed) {
                $jumlahKg = ($feed['rasio'] / $totalRasio) * $request->pakan_aktual;

                // Memakai nama fungsi relasi yang ada di model Anda (details)
                $feedLog->details()->create([
                    'inventory_id' => $feed['inventory_id'],
                    'rasio' => $feed['rasio'],
                    'jumlah_kg' => round($jumlahKg, 2),
                ]);

                $inventory = Inventory::find($feed['inventory_id']);
                if ($inventory) {
                    $sisaStok = $inventory->total_stok_kg - $jumlahKg;
                    $inventory->update([
                        'total_stok_kg' => round($sisaStok, 2)
                    ]);
                }
            }

            \DB::commit();
            
            // Redirect dikembalikan ke feedlog.index sesuai file asli Anda
            return redirect()->route('feedlog.index')->with('message', 'Pemberian pakan berhasil dicatat!');

        } catch (\Exception $e) {
            \DB::rollBack();
            
            // =========================================================
            // 3. RADAR ERROR DATABASE
            // =========================================================
            // Jika layar menjadi hitam dan menampilkan ini, berarti kolom database ada yang salah/belum dimigrasi!
            dd('GAGAL MENYIMPAN KE DATABASE!', 'PESAN ERROR: ' . $e->getMessage(), 'BARIS: ' . $e->getLine());
        }
    }
}