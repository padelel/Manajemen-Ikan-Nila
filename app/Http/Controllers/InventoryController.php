<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Inertia\Inertia;
use Illuminate\Http\Request;

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
        return Inertia::render('Inventory/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pakan' => 'required|string|max:255',
            'total_stok_kg' => 'required|numeric|min:0',
            'terakhir_update' => 'required|date',
        ]);

        Inventory::create($validated);
        return redirect()->route('inventory.index');
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
}