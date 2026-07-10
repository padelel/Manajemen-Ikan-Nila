<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\ParameterHarian;
use App\Models\SiklusBudidaya;
use App\Models\TebarLog;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TebarLogController extends Controller
{
    public function index()
    {
        $query = TebarLog::with(['kolam', 'user'])->latest('tanggal_tebar');

        if (auth()->user()->role === 'operator') {
            $kolamIds = auth()->user()->kolams()->pluck('kolams.id');
            $query->whereIn('kolam_id', $kolamIds);
        }

        return Inertia::render('TebarLog/Index', [
            'logs' => $query->get(),
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        // Ambil kolam yang bisa diakses (Semua untuk SPV, Sesuai pivot untuk Operator)
        $kolamQuery = Kolam::query();
        if ($user->role === 'operator') {
            $kolamIds = $user->kolams()->pluck('kolams.id');
            $kolamQuery->whereIn('id', $kolamIds);
        }

        // Mapping status kesiapan setiap kolam
        $kolams = $kolamQuery->get()->map(function ($kolam) {
            // 1. Syarat 1: Tidak boleh ada siklus berjalan
            $adaSiklus = SiklusBudidaya::where('kolam_id', $kolam->id)
                ->where('status_aktif', 'berjalan')
                ->exists();

            // 2. Syarat 2: Wajib ada pengecekan air dalam 24 jam terakhir
            $cekTerbaru = ParameterHarian::where('kolam_id', $kolam->id)
                ->where('tanggal_cek', '>=', Carbon::now()->subHours(24))
                ->exists();

            // 3. Syarat 3: Tidak boleh ada tiket mitigasi AI yang menggantung
            $tiketAktif = Tiket::where('kolam_id', $kolam->id)
                ->whereNotIn('status', ['selesai'])
                ->count();

            $status_kesiapan = 'siap';
            $pesan_kesiapan = 'Kondisi air optimal. Kolam siap ditebar benih.';

            if ($adaSiklus) {
                $status_kesiapan = 'tidak_siap';
                $pesan_kesiapan = 'Kolam masih memiliki siklus berjalan (Belum panen total).';
            } elseif (! $cekTerbaru) {
                $status_kesiapan = 'tidak_siap';
                $pesan_kesiapan = 'Kualitas air belum dicek hari ini. Lakukan input kualitas air terlebih dahulu.';
            } elseif ($tiketAktif > 0) {
                $status_kesiapan = 'tidak_siap';
                $pesan_kesiapan = "Terdapat $tiketAktif tiket mitigasi air aktif. Selesaikan masalah air tersebut terlebih dahulu.";
            }

            return [
                'id' => $kolam->id,
                'nama_kolam' => $kolam->nama_kolam,
                'lokasi' => $kolam->lokasi,
                'status_kesiapan' => $status_kesiapan,
                'pesan_kesiapan' => $pesan_kesiapan,
            ];
        });

        return Inertia::render('TebarLog/Create', [
            'kolams' => $kolams,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'tanggal_tebar' => 'required|date|before_or_equal:today',
            'jumlah_ikan' => 'required|integer|min:1',
            'sumber_benih' => 'nullable|string|max:255',
        ]);

        // Validasi Kemanan Backend (Cegah bypass dari Inspect Element)
        $adaSiklus = SiklusBudidaya::where('kolam_id', $request->kolam_id)->where('status_aktif', 'berjalan')->exists();
        if ($adaSiklus) {
            return back()->withErrors(['kolam_id' => 'Kolam ini masih memiliki siklus berjalan.']);
        }

        $cekTerbaru = ParameterHarian::where('kolam_id', $request->kolam_id)->where('tanggal_cek', '>=', Carbon::now()->subHours(24))->exists();
        if (! $cekTerbaru) {
            return back()->withErrors(['kolam_id' => 'Kualitas air belum dicek hari ini.']);
        }

        $tiketAktif = Tiket::where('kolam_id', $request->kolam_id)->whereNotIn('status', ['selesai'])->exists();
        if ($tiketAktif) {
            return back()->withErrors(['kolam_id' => 'Selesaikan tiket mitigasi air terlebih dahulu.']);
        }

        DB::transaction(function () use ($request) {
            // 1. Buat Log Tebar
            TebarLog::create([
                'kolam_id' => $request->kolam_id,
                'user_id' => auth()->id(),
                'tanggal_tebar' => $request->tanggal_tebar,
                'jumlah_ikan' => $request->jumlah_ikan,
                'berat_rata_gram' => 0,
                'sumber_benih' => $request->sumber_benih ?? 'Internal',
            ]);

            // 2. Otomatis Mulai Siklus Budidaya Baru
            SiklusBudidaya::create([
                'kolam_id' => $request->kolam_id,
                'tanggal_mulai' => $request->tanggal_tebar,
                'jumlah_tebar_awal' => $request->jumlah_ikan,
                'status_aktif' => 'berjalan',
            ]);

            // 3. Update Status Kolam dan populasi
            Kolam::where('id', $request->kolam_id)->update([
                'status_kolam' => 'aktif',
                'jumlah_ikan' => $request->jumlah_ikan,
            ]);
        });

        return redirect()->route('tebar.index')->with('success', 'Tebar benih berhasil dicatat dan siklus baru otomatis dimulai.');
    }
}
