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
    public function index()
    {
        // Menampilkan riwayat pemberian pakan
        $logs = FeedLog::with(['kolam', 'rule', 'inventory', 'user'])->latest()->get();
        return Inertia::render('FeedLog/Index', ['logs' => $logs]);
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
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'rule_id' => 'required|exists:rules,id',
            'inventory_id' => 'required|exists:inventories,id',
            'tanggal_pakan' => 'required|date',
            'rekomendasi_sistem' => 'required|numeric',
            'pakan_aktual' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = Auth::id();

        // 1. Simpan Riwayat Pakan
        FeedLog::create($validated);

        // 2. Potong Stok di Gudang secara otomatis
        $inventory = Inventory::find($request->inventory_id);
        $inventory->total_stok_kg -= $request->pakan_aktual;
        $inventory->save();

        return redirect()->route('feedlog.index');
    }
}