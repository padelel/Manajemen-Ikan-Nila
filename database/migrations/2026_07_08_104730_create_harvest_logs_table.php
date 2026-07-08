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
        Schema::create('harvest_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->onDelete('cascade');
            $table->foreignId('siklus_budidaya_id')->constrained('siklus_budidayas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('jenis_panen', ['parsial', 'total']);
            $table->date('tanggal_panen');
            $table->integer('jumlah_ikan_panen');
            $table->decimal('berat_total_kg', 10, 2);
            $table->decimal('survival_rate', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_logs');
    }
};
