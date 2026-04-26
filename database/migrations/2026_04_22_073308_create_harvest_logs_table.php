<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->enum('jenis_panen', ['Parsial', 'Full']);
            $table->date('tanggal_panen');
            $table->integer('jumlah_ikan')->comment('Jumlah ikan yang dipanen (Ekor)');
            $table->decimal('berat_total_kg', 10, 2)->comment('Total berat ikan yang dipanen (Kg)');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_logs');
    }
};