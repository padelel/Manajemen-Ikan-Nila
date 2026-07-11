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
        Schema::create('inferensi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->onDelete('cascade');
            $table->foreignId('parameter_harian_id')->constrained('parameter_harians')->onDelete('cascade');
            $table->date('tanggal_inferensi');
            $table->json('fakta_input');
            $table->json('fakta_baru');
            $table->json('rule_terpicu');
            $table->json('kode_diagnosa')->nullable();
            $table->json('label_diagnosa')->nullable();
            $table->json('kode_kesimpulan')->nullable();
            $table->json('tindakan_mitigasi')->nullable();
            $table->json('peringatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inferensi_logs');
    }
};
