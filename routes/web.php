<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HarvestLogController;
use App\Http\Controllers\KolamController;
use App\Http\Controllers\MortalityLogController;
use App\Http\Controllers\ParameterHarianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TebarLogController;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    // ================================================================
    // 1. RUTE BERSAMA (BISA DIAKSES SUPERVISOR & OPERATOR)
    // ================================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tiket Mitigasi (melihat daftar & detail)
    Route::get('/tiket', [TiketController::class, 'index'])->name('tiket.index');
    Route::get('/tiket/{tiket}', [TiketController::class, 'show'])->name('tiket.show');

    // Parameter Harian (melihat riwayat)
    Route::get('/parameter', [ParameterHarianController::class, 'index'])->name('parameter.index');

    // Log Aktivitas (melihat riwayat)
    Route::get('/tebar', [TebarLogController::class, 'index'])->name('tebar.index');
    Route::get('/kematian', [MortalityLogController::class, 'index'])->name('kematian.index');

    // Rute Index Panen (Bersama)
    Route::get('/panen', [HarvestLogController::class, 'index'])->name('panen.index');

    // Analisis & Laporan (DIPINDAHKAN KE SINI AGAR OPERATOR BISA LIHAT)
    Route::get('/analisis', [ReportController::class, 'index'])->name('analisis.index');
    Route::get('/analisis/{id}', [ReportController::class, 'show'])->name('analisis.show');

    // ================================================================
    // 2. KHUSUS SUPERVISOR (PENGELOLA UTAMA)
    // ================================================================
    Route::middleware('role:supervisor')->group(function () {

        // Manajemen Pengguna
        Route::resource('users', UserController::class);

        // Manajemen Kolam (Master Data) + penugasan operator
        Route::resource('kolam', KolamController::class);
        Route::post('/kolam/{kolam}/assign', [KolamController::class, 'assignOperators'])->name('kolam.assign');

        // Verifikasi Tiket Mitigasi
        Route::post('/tiket/{tiket}/verifikasi', [TiketController::class, 'verifikasi'])->name('tiket.verifikasi');
    });

    // ================================================================
    // 3. KHUSUS OPERATOR LAPANGAN (TUGAS INPUT DATA)
    // ================================================================
    Route::middleware('role:operator')->group(function () {

        // Input Parameter Air Harian
        Route::get('/parameter/create', [ParameterHarianController::class, 'create'])->name('parameter.create');
        Route::post('/parameter', [ParameterHarianController::class, 'store'])->name('parameter.store');

        // Selesaikan Tiket Mitigasi
        Route::post('/tiket/{tiket}/selesaikan', [TiketController::class, 'selesaikan'])->name('tiket.selesaikan');

        // Pencatatan Log Aktivitas
        Route::resource('tebar', TebarLogController::class)->only(['create', 'store']);
        Route::resource('kematian', MortalityLogController::class)->only(['create', 'store']);

        // Pencatatan Panen (Berbasis Siklus)
        Route::get('/panen/{siklus_id}/create', [HarvestLogController::class, 'create'])->name('panen.create');
        Route::post('/panen/{siklus_id}', [HarvestLogController::class, 'store'])->name('panen.store');
    });

    // Detail Diagnosa Kualitas Air (ditempatkan setelah rute operator agar tidak menimpa /parameter/create)
    Route::middleware('role:supervisor')->group(function () {
        Route::get('/parameter/{parameter}', [ParameterHarianController::class, 'show'])->name('parameter.show');
    });
});

require __DIR__.'/auth.php';
