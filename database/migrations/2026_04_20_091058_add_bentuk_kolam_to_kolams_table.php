<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kolams', function (Blueprint $table) {
            // 1. Hapus kolom 'dimensi' yang lama karena sudah dipisah
            if (Schema::hasColumn('kolams', 'dimensi')) {
                $table->dropColumn('dimensi');
            }

            // 2. Tambahkan semua kolom baru yang dibutuhkan oleh sistem
            $table->string('lokasi')->nullable()->after('nama_kolam');
            $table->enum('bentuk_kolam', ['Bundar', 'Persegi'])->default('Bundar')->after('lokasi');
            $table->string('status_kolam')->default('Aktif')->after('bentuk_kolam');
            $table->float('panjang_m')->default(0)->after('status_kolam');
            $table->float('lebar_m')->default(0)->after('panjang_m');
            $table->float('kedalaman_m')->default(0)->after('lebar_m');
            $table->date('tanggal_tebar')->nullable()->after('kedalaman_m');
        });
    }

    public function down(): void
    {
        Schema::table('kolams', function (Blueprint $table) {
            $table->string('dimensi')->nullable();
            $table->dropColumn([
                'lokasi', 'bentuk_kolam', 'status_kolam', 
                'panjang_m', 'lebar_m', 'kedalaman_m', 'tanggal_tebar'
            ]);
        });
    }
};