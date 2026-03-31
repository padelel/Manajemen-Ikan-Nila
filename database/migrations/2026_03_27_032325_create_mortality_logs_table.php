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
        Schema::create('mortality_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Siapa yang lapor
            
            $table->date('tanggal_kematian');
            $table->integer('jumlah_mati');
            $table->text('catatan')->nullable(); // Misal: "Terapung di pojok kolam"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortality_logs');
    }
};
