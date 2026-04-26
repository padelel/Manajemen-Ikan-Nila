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
        // database/migrations/xxxx_create_tebar_logs_table.php
        Schema::create('tebar_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->date('tanggal_tebar');
            $table->integer('jumlah_ikan');
            $table->decimal('berat_rata_gram', 8, 2);
            $table->string('sumber_benih')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tebar_logs');
    }
};
