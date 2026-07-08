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
        Schema::create('parameter_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_cek');
            $table->decimal('suhu', 5, 2);
            $table->decimal('ph', 4, 2);
            $table->decimal('do_mgl', 5, 2);
            $table->decimal('amonia_mgl', 6, 4);
            $table->decimal('flok_ml', 6, 2);
            $table->decimal('kecerahan_cm', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_harians');
    }
};
