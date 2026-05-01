<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Index', [
            'inventories' => Inventory::all()
        ]);
    }

    public function create()
    {
        // Lempar data pakan yang sudah ada ke Vue untuk pilihan Restock
        return Inertia::render('Inventory/Create', [
            'inventories' => Inventory::all()
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi dinamis berdasarkan mode yang dipilih
        $validated = $request->validate([
            'mode'         => 'required|in:restock,baru',
            'inventory_id' => 'required_if:mode,restock|nullable|exists:inventories,id',
            'nama_pakan'   => 'required_if:mode,baru|nullable|string|max:255',
            'jumlah_kg'    => 'required|numeric|min:0.1',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $inventory = null;

            if ($request->mode === 'restock') {
                // A. JIKA RESTOCK: Cari pakan, lalu tambahkan stoknya
                $inventory = Inventory::findOrFail($request->inventory_id);
                $inventory->increment('total_stok_kg', $request->jumlah_kg);
                $inventory->update(['terakhir_update' => now()]);
            } else {
                // B. JIKA BARU: Buat master pakan baru di database
                $inventory = Inventory::create([
                    'nama_pakan'      => $request->nama_pakan,
                    'total_stok_kg'   => $request->jumlah_kg,
                    'terakhir_update' => now(),
                ]);
            }

            // 2. CATAT KE LOG HISTORI GUDANG (Penting!)
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'user_id'      => Auth::id(),
                'tipe'         => 'Masuk',
                'jumlah'       => $request->jumlah_kg,
                'keterangan'   => $request->keterangan ?? ($request->mode === 'restock' ? 'Restock rutin.' : 'Pendaftaran merek pakan baru.'),
            ]);

            DB::commit();

            return redirect()->route('inventory.index')->with('success', 'Stok pakan berhasil ditambahkan dan dicatat di riwayat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Inventory $inventory)
    {
        return Inertia::render('Inventory/Edit', [
            'inventory' => $inventory
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'nama_pakan' => 'required|string|max:255',
            'total_stok_kg' => 'required|numeric|min:0',
            'terakhir_update' => 'required|date',
        ]);

        $inventory->update($validated);
        return redirect()->route('inventory.index');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index');
    }

    public function history()
    {
        // Mengambil data log beserta relasi pakan (inventory) dan siapa yang mencatat (user)
        // Pastikan Anda sudah membuat Model InventoryLog
        $logs = \App\Models\InventoryLog::with(['inventory', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return \Inertia\Inertia::render('Inventory/History', [
            'logs' => $logs
        ]);
    }
}