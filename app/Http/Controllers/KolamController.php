<?php

namespace App\Http\Controllers;

use App\Models\Kolam;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KolamController extends Controller
{
    public function index()
    {
        $kolams = Kolam::latest()->get();
        return Inertia::render('Kolam/Index', [
            'kolams' => $kolams
        ]);
    }

    public function create()
    {
        return Inertia::render('Kolam/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kolam'   => 'required|string|max:100',
            'lokasi'       => 'nullable|string|max:150',
            'panjang_m'    => 'required|numeric|min:1',
            'lebar_m'      => 'required|numeric|min:1',
            'kedalaman_m'  => 'required|numeric|min:0.5',
            'status_kolam' => 'required|in:aktif,tidak aktif,maintenance',
        ]);

        Kolam::create($validated);

        return redirect()->route('kolam.index')->with('success', 'Data Kolam berhasil ditambahkan.');
    }

    public function edit(Kolam $kolam)
    {
        return Inertia::render('Kolam/Edit', [
            'kolam' => $kolam
        ]);
    }

    public function update(Request $request, Kolam $kolam)
    {
        $validated = $request->validate([
            'nama_kolam'   => 'required|string|max:100',
            'lokasi'       => 'nullable|string|max:150',
            'panjang_m'    => 'required|numeric|min:1',
            'lebar_m'      => 'required|numeric|min:1',
            'kedalaman_m'  => 'required|numeric|min:0.5',
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
}