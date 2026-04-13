<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KolamController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DailyParameterController;
use App\Http\Controllers\FeedLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyOperationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Rute Dashboard menggunakan Controller baru
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rute yang HANYA BISA DIAKSES ADMIN
Route::middleware('can:admin')->group(function () {

    // Rute Data Kolam (Admin Only)
    Route::get('/kolam', [KolamController::class, 'index'])->name('kolam.index');
    Route::get('/kolam/create', [KolamController::class, 'create'])->name('kolam.create');
    Route::post('/kolam', [KolamController::class, 'store'])->name('kolam.store');
    Route::get('/kolam/{kolam}/edit', [KolamController::class, 'edit'])->name('kolam.edit');
    Route::put('/kolam/{kolam}', [KolamController::class, 'update'])->name('kolam.update');
    Route::delete('/kolam/{kolam}', [KolamController::class, 'destroy'])->name('kolam.destroy');

    // Rute Gudang Pakan (Admin Only)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/laporan/cetak', [ReportController::class, 'cetakPDF'])->name('laporan.cetak');

    // Rute Data Akun Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Data Kolam
    // Route::get('/kolam', [KolamController::class, 'index'])->name('kolam.index');
    // Route::get('/kolam/create', [KolamController::class, 'create'])->name('kolam.create');
    // Route::post('/kolam', [KolamController::class, 'store'])->name('kolam.store');
    // Route::get('/kolam/{kolam}/edit', [KolamController::class, 'edit'])->name('kolam.edit');
    // Route::put('/kolam/{kolam}', [KolamController::class, 'update'])->name('kolam.update');
    // Route::delete('/kolam/{kolam}', [KolamController::class, 'destroy'])->name('kolam.destroy');

    // Rute Gudang Pakan
    // Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    // Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    // Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    // Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    // Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    // Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Rute Parameter Air
    Route::get('/parameter', [DailyParameterController::class, 'index'])->name('parameter.index');
    Route::get('/parameter/create', [DailyParameterController::class, 'create'])->name('parameter.create');
    Route::post('/parameter', [DailyParameterController::class, 'store'])->name('parameter.store');

    // Rute Manajemen Pakan (Forward Chaining)
    Route::get('/feedlog', [FeedLogController::class, 'index'])->name('feedlog.index');
    Route::get('/feedlog/create', [FeedLogController::class, 'create'])->name('feedlog.create');
    Route::post('/feedlog', [FeedLogController::class, 'store'])->name('feedlog.store');

    // Rute khusus untuk mengecek rekomendasi sistem secara Real-Time tanpa refresh halaman
    Route::get('/api/hitung-pakan/{kolam_id}', [FeedLogController::class, 'hitungRekomendasi']);

    // Rute Mortalitas / Kematian Ikan
    Route::get('/kematian', [MortalityLogController::class, 'index'])->name('kematian.index');
    Route::get('/kematian/create', [MortalityLogController::class, 'create'])->name('kematian.create');
    Route::post('/kematian', [MortalityLogController::class, 'store'])->name('kematian.store');

    // Rute Operasi Harian Terpadu
    Route::get('/operasi-harian', [DailyOperationController::class, 'create'])->name('operasi.create');
    Route::post('/operasi-harian', [DailyOperationController::class, 'store'])->name('operasi.store');
});

require __DIR__.'/auth.php';
