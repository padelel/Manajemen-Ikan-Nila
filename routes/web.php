<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KolamController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DailyParameterController;
use App\Http\Controllers\FeedLogController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\HarvestLogController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TebarLogController;
use App\Http\Controllers\DailyOperationController;
use Illuminate\Support\Facades\Route;

// Redirect halaman awal langsung ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    // ================================================================
    // 1. RUTE BERSAMA (BISA DIAKSES ADMIN & OPERATOR)
    // ================================================================
    // Halaman Ringkasan Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengaturan Akun
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // HALAMAN MONITORING (Semua bisa melihat data/tabel riwayat)
    Route::get('/parameter', [DailyParameterController::class, 'index'])->name('parameter.index');
    Route::get('/feedlog', [FeedLogController::class, 'index'])->name('feedlog.index');
    Route::get('/kematian', [MortalityLogController::class, 'index'])->name('kematian.index');
    Route::get('/panen', [HarvestLogController::class, 'index'])->name('panen.index');
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/tebar', [TebarLogController::class, 'index'])->name('tebar.index');
    Route::resource('inventory', InventoryController::class);


    // ================================================================
    // 2. KHUSUS ADMIN (PENGELOLA UTAMA)
    // ================================================================
    Route::middleware('role:admin')->group(function () {

        // Riwayat Stok Pakan (WAJIB diletakkan sebelum resource 'inventory')
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        
        // Admin memegang penuh kendali Master Data (Tambah/Edit/Hapus Kolam & Gudang)
        Route::resource('kolam', KolamController::class);
        // Route::resource('inventory', InventoryController::class);

    });


    // ================================================================
    // 3. KHUSUS OPERATOR LAPANGAN (TUGAS INPUT DATA)
    // ================================================================
    Route::middleware('role:operator')->group(function () {
        
        // Operasi Harian (Input Cepat Multi-Kolam)
        Route::get('/operasi-harian', [DailyOperationController::class, 'create'])->name('operasi.create');
        Route::post('/operasi-harian', [DailyOperationController::class, 'store'])->name('operasi.store');

        // Fungsi Input Form (Create, Store, Edit, Update, Destroy)
        // Kita mengecualikan 'index' dan 'show' karena rute index sudah dideklarasikan di Rute Bersama
        Route::resource('parameter', DailyParameterController::class)->except(['index', 'show']);
        Route::resource('feedlog', FeedLogController::class)->except(['index', 'show']);
        Route::resource('kematian', MortalityLogController::class)->except(['index', 'show']);
        Route::resource('panen', HarvestLogController::class)->except(['index', 'show']);
        Route::resource('tebar', TebarLogController::class)->except(['index', 'show']);

        // Transfer Ikan
        Route::get('/transfer/create', [TransferController::class, 'create'])->name('transfer.create');
        Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');

    });

});

require __DIR__.'/auth.php';