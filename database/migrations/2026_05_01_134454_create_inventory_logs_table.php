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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Menggunakan enum agar datanya pasti antara Masuk (Restock) atau Keluar (Penggunaan)
            $table->enum('tipe', ['Masuk', 'Keluar']);
            
            // Menggunakan decimal agar bisa menyimpan angka desimal (misal: 12.5 Kg)
            $table->decimal('jumlah', 10, 2);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};