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
        // PERBAIKAN: Menambahkan 'user' ke dalam 'with' agar nama pelapor tidak error
        $logs = FeedLog::with(['kolam', 'user', 'details.inventory'])->latest()->get();
        return Inertia::render('FeedLog/Index', ['feedLogs' => $logs]);
    }

    public function create()
    {
        return Inertia::render('FeedLog/Create', [
            'kolams' => Kolam::all(),
            'inventories' => Inventory::where('total_stok_kg', '>', 0)->get(),
        ]);
    }

    public function hitungRekomendasi($kolam_id)
    {
        $kolam = Kolam::find($kolam_id);
        $parameter = DailyParameter::where('kolam_id', $kolam_id)->latest('tanggal_cek')->first();
        $biomassa_kg = ($kolam->jumlah_ikan * $kolam->berat_rata_gram) / 1000;
        $dosis_harian_kg = $biomassa_kg * 0.05; 
        $dosis_dasar_kg = $dosis_harian_kg / 2; 

        if (!$parameter) {
            return response()->json(['error' => 'Data kualitas air belum diinput untuk kolam ini.']);
        }

        $suhu = $parameter->suhu;
        $ph = $parameter->ph;
        $visual = $parameter->kondisi_visual;
        $rule = Rule::where('kode_rule', 'R01')->first(); 
        $persentase = 100;

        if ($ph < 6.5 || $ph > 8.5 || $visual == 'Berbusa') {
            $rule = Rule::where('kode_rule', 'R03')->first();
            $persentase = 0;
        } elseif ($suhu < 25 || $suhu > 32 || $visual == 'Keruh') {
            $rule = Rule::where('kode_rule', 'R02')->first();
            $persentase = 50;
        }

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
        // PERBAIKAN: Menambahkan validasi 'frekuensi'
        $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'rule_id' => 'required',
            'tanggal_pakan' => 'required|date',
            'frekuensi' => 'required|integer|min:1', 
            'rekomendasi_sistem' => 'required|numeric',
            'pakan_aktual' => 'required|numeric|min:0.1',
            'feeds' => 'required|array|min:1',
            'feeds.*.inventory_id' => 'required|exists:inventories,id',
            'feeds.*.rasio' => 'required|integer|min:1',
        ]);

        \DB::beginTransaction();
        try {
            // PERBAIKAN: Menyimpan data 'frekuensi'
            $feedLog = FeedLog::create([
                'user_id' => Auth::id(),
                'kolam_id' => $request->kolam_id,
                'rule_id' => $request->rule_id,
                'tanggal_pakan' => $request->tanggal_pakan,
                'frekuensi' => $request->frekuensi, 
                'rekomendasi_sistem' => $request->rekomendasi_sistem,
                'pakan_aktual' => $request->pakan_aktual,
            ]);

            $totalRasio = collect($request->feeds)->sum('rasio');

            foreach ($request->feeds as $feed) {
                $jumlahKg = ($feed['rasio'] / $totalRasio) * $request->pakan_aktual;

                $feedLog->details()->create([
                    'inventory_id' => $feed['inventory_id'],
                    'rasio' => $feed['rasio'],
                    'jumlah_kg' => round($jumlahKg, 2),
                ]);

                $inventory = Inventory::find($feed['inventory_id']);
                $sisaStok = $inventory->total_stok_kg - $jumlahKg;
                
                $inventory->update([
                    'total_stok_kg' => round($sisaStok, 2)
                ]);
            }

            \DB::commit();
            return redirect()->route('feedlog.index')->with('message', 'Pemberian pakan berhasil dicatat!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}