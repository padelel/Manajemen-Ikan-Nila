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
        Schema::create('daily_parameters', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_cek');
            $table->float('suhu')->nullable();
            $table->float('ph')->nullable();
            $table->enum('kondisi_visual', ['Jernih', 'Keruh', 'Berbusa'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_parameters');
    }
};
