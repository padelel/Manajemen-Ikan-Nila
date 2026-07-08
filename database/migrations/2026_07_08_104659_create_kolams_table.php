<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kolams', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kolam', 100);
            $table->string('lokasi', 150)->nullable();
            $table->decimal('panjang_m', 8, 2)->nullable();
            $table->decimal('lebar_m', 8, 2)->nullable();
            $table->decimal('kedalaman_m', 8, 2)->nullable();
            $table->enum('status_kolam', ['aktif', 'tidak aktif', 'maintenance'])->default('tidak aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kolams');
    }
};
