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
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    // ================================================================
    // 1. RUTE BERSAMA (BISA DIAKSES ADMIN & OPERATOR)
    // ================================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/parameter', [DailyParameterController::class, 'index'])->name('parameter.index');
    Route::get('/feedlog', [FeedLogController::class, 'index'])->name('feedlog.index');
    Route::get('/kematian', [MortalityLogController::class, 'index'])->name('kematian.index');
    Route::get('/panen', [HarvestLogController::class, 'index'])->name('panen.index');
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/tebar', [TebarLogController::class, 'index'])->name('tebar.index');
    Route::get('/analisis', [\App\Http\Controllers\ReportController::class, 'index'])->name('analisis.index');
    
    // AKSES MELIHAT & EDIT DATA KOLAM
    Route::get('/kolam', [KolamController::class, 'index'])->name('kolam.index');
    Route::get('/kolam/{kolam}/edit', [KolamController::class, 'edit'])->name('kolam.edit');
    Route::put('/kolam/{kolam}', [KolamController::class, 'update'])->name('kolam.update'); // Jika Anda pakai method PUT/PATCH


    // ================================================================
    // 2. KHUSUS ADMIN (PENGELOLA UTAMA)
    // ================================================================
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'edit', 'update']);
        Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');
        
        // Manajemen Master Data Kolam (Hanya untuk Tambah dan Hapus)
        Route::resource('kolam', KolamController::class)->only(['create', 'store', 'destroy']);
    });


    // ================================================================
    // 3. KHUSUS OPERATOR LAPANGAN (TUGAS INPUT DATA)
    // ================================================================
    Route::middleware('role:operator')->group(function () {
        Route::get('/operasi-harian', [DailyOperationController::class, 'create'])->name('operasi.create');
        Route::post('/operasi-harian', [DailyOperationController::class, 'store'])->name('operasi.store');

        Route::resource('inventory', InventoryController::class)->except(['index', 'show']);
        Route::resource('parameter', DailyParameterController::class)->except(['index', 'show']);
        Route::resource('feedlog', FeedLogController::class)->except(['index', 'show']);
        Route::resource('kematian', MortalityLogController::class)->except(['index', 'show']);
        Route::resource('panen', HarvestLogController::class)->except(['index', 'show']);
        Route::resource('tebar', TebarLogController::class)->except(['index', 'show']);

        Route::get('/transfer/create', [TransferController::class, 'create'])->name('transfer.create');
        Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');
    });

});

require __DIR__.'/auth.php';