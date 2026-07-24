<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\ParameterHarian;
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

    public function index(Request $request)
    {
        $query = ParameterHarian::with(['kolam', 'inferensiLog']);

        $query->when($request->query('kolam_id'), function ($query, $kolamId) {
            $query->where('kolam_id', $kolamId);
        });

        $query->when($request->query('start_date'), function ($query, $startDate) {
            $query->whereDate('tanggal_cek', '>=', $startDate);
        });

        $query->when($request->query('end_date'), function ($query, $endDate) {
            $query->whereDate('tanggal_cek', '<=', $endDate);
        });

        $riwayat = $query->latest()->get();
        $kolams = Kolam::all();

        return Inertia::render('Parameter/Index', [
            'riwayat' => $riwayat,
            'kolams' => $kolams,
            'filters' => [
                'kolam_id' => $request->query('kolam_id', ''),
                'start_date' => $request->query('start_date', ''),
                'end_date' => $request->query('end_date', ''),
            ],
        ]);
    }

    public function show($id)
    {
        $parameter = ParameterHarian::with([
            'kolam',
            'user',
            'inferensiLog.tiket',
        ])->findOrFail($id);

        return Inertia::render('Parameter/Show', [
            'parameter' => $parameter,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->role === 'operator') {
            $kolams = $user->kolams;
        } else {
            $kolams = Kolam::all();
        }

        return Inertia::render('Parameter/Create', [
            'kolams' => $kolams,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input dari Operator
        $validated = $request->validate([
            'kolam_id' => 'required|exists:kolams,id',
            'tanggal_cek' => 'required|date',
            'suhu' => 'required|numeric',
            'ph' => 'required|numeric',
            'do_mgl' => 'required|numeric',
            'amonia_mgl' => 'required|numeric',
            'flok_ml' => 'required|numeric',

        ]);

        if (auth()->user()->role === 'operator') {
            $isAssigned = auth()->user()->kolams()->where('kolam_id', $validated['kolam_id'])->exists();
            if (! $isAssigned) {
                return back()->withErrors(['kolam_id' => 'Anda tidak ditugaskan ke kolam ini.'])->withInput();
            }
        }

        $validated['user_id'] = auth()->id();

        // 2. Simpan Data Fakta Dasar ke Database
        $parameter = ParameterHarian::create($validated);

        // 3. JALANKAN OTAK SISTEM (Forward Chaining)
        // Service ini akan otomatis mengevaluasi air dan membuat Tiket jika ada masalah
        $logInferensi = $this->fcService->prosesInferensi($parameter);

        // 4. Redirect kembali dengan membawa pesan hasil diagnosa
        if (! in_array('DN', $logInferensi->kode_diagnosa ?? ['DN'])) {
            $labelText = implode('; ', $logInferensi->label_diagnosa ?? ['Unknown']);

            return redirect()->route('parameter.index')->with('warning', 'Peringatan: '.$labelText.'. Tiket mitigasi telah diterbitkan!');
        }

        return redirect()->route('parameter.index')->with('success', 'Kualitas air normal. Data berhasil disimpan.');
    }
}
