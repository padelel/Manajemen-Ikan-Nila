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
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->onDelete('cascade');
            $table->foreignId('inferensi_log_id')->constrained('inferensi_logs')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('users');
            $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->string('judul', 150);
            $table->text('deskripsi_tindakan');
            $table->enum('status', ['open', 'in_progress', 'menunggu_verifikasi', 'selesai'])->default('open');
            $table->dateTime('deadline');
            $table->text('bukti_penyelesaian')->nullable();
            $table->text('catatan_supervisor')->nullable();
            $table->dateTime('diselesaikan_at')->nullable();
            $table->dateTime('diverifikasi_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tikets');
    }
};
