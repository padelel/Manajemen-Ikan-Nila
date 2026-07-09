<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KolamController extends Controller
{
    public function index()
    {
        // Ambil data kolam beserta operator yang sedang bertugas
        $kolams = Kolam::with('operators')->orderBy('created_at', 'desc')->get();

        // Ambil daftar user yang memiliki role 'operator' untuk ditampilkan di Modal
        $operators = User::where('role', 'operator')->get();

        return Inertia::render('Kolam/Index', [
            'kolams' => $kolams,
            'operators' => $operators,
        ]);
    }

    public function create()
    {
        return Inertia::render('Kolam/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:150',
            'panjang_m' => 'required|numeric|min:1',
            'lebar_m' => 'required|numeric|min:1',
            'kedalaman_m' => 'required|numeric|min:0.5',
            'status_kolam' => 'required|in:aktif,tidak aktif,maintenance',
        ]);

        Kolam::create($validated);

        return redirect()->route('kolam.index')->with('success', 'Data Kolam berhasil ditambahkan.');
    }

    public function edit(Kolam $kolam)
    {
        return Inertia::render('Kolam/Edit', [
            'kolam' => $kolam,
        ]);
    }

    public function update(Request $request, Kolam $kolam)
    {
        $validated = $request->validate([
            'nama_kolam' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:150',
            'panjang_m' => 'required|numeric|min:1',
            'lebar_m' => 'required|numeric|min:1',
            'kedalaman_m' => 'required|numeric|min:0.5',
            'status_kolam' => 'required|in:aktif,tidak aktif,maintenance',
        ]);

        $kolam->update($validated);

        return redirect()->route('kolam.index')->with('success', 'Data Kolam berhasil diperbarui.');
    }

    public function destroy(Kolam $kolam)
    {
        $kolam->delete();

        return redirect()->route('kolam.index')->with('success', 'Data Kolam berhasil dihapus.');
    }

    public function assignOperators(Request $request, Kolam $kolam)
    {
        $request->validate([
            'operator_ids' => 'array',
            'operator_ids.*' => 'exists:users,id',
        ]);

        $syncData = [];
        // Siapkan data pivot (tanggal_penugasan) untuk setiap operator yang dipilih
        foreach ($request->operator_ids ?? [] as $id) {
            $syncData[$id] = ['tanggal_penugasan' => Carbon::today()->toDateString()];
        }

        // Fungsi sync() otomatis menghapus yang tidak diceklis dan menambahkan yang baru
        $kolam->operators()->sync($syncData);

        return back()->with('success', 'Penugasan operator berhasil diperbarui.');
    }
}
