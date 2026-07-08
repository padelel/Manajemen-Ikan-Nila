<?php

namespace App\Http\Controllers;

use App\Models\ParameterHarian;
use App\Models\Kolam;
use App\Services\ForwardChainingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParameterHarianController extends Controller
{
    protected $fcService;

    // Inject Forward Chaining Service ke dalam Controller
    public function __construct(ForwardChainingService $fcService)
    {
        $this->fcService = $fcService;
    }

    public function index()
    {
        // Tampilkan riwayat parameter air beserta hasil inferensinya
        $riwayat = ParameterHarian::with(['kolam', 'inferensiLog'])->latest()->get();
        return Inertia::render('Parameter/Index', [
            'riwayat' => $riwayat
        ]);
    }

    public function create()
    {
        // Hanya tampilkan kolam yang berstatus aktif
        $kolams = Kolam::where('status_kolam', 'aktif')->get();
        return Inertia::render('Parameter/Create', [
            'kolams' => $kolams
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input dari Operator
        $validated = $request->validate([
            'kolam_id'     => 'required|exists:kolams,id',
            'tanggal_cek'  => 'required|date',
            'suhu'         => 'required|numeric',
            'ph'           => 'required|numeric',
            'do_mgl'       => 'required|numeric',
            'amonia_mgl'   => 'required|numeric',
            'flok_ml'      => 'required|numeric',
            'kecerahan_cm' => 'required|numeric',
        ]);

        $validated['user_id'] = auth()->id();

        // 2. Simpan Data Fakta Dasar ke Database
        $parameter = ParameterHarian::create($validated);

        // 3. JALANKAN OTAK SISTEM (Forward Chaining)
        // Service ini akan otomatis mengevaluasi air dan membuat Tiket jika ada masalah
        $logInferensi = $this->fcService->prosesInferensi($parameter);

        // 4. Redirect kembali dengan membawa pesan hasil diagnosa
        if ($logInferensi->kode_diagnosa !== 'D-NORMAL') {
            return redirect()->route('parameter.index')->with('warning', 'Peringatan: ' . $logInferensi->label_diagnosa . '. Tiket mitigasi telah diterbitkan!');
        }

        return redirect()->route('parameter.index')->with('success', 'Kualitas air normal. Data berhasil disimpan.');
    }
}