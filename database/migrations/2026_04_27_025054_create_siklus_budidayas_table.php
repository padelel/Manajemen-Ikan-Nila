<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('siklus_budidayas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('status_aktif')->default('Aktif'); // 'Aktif' atau 'Selesai'
            $table->integer('jumlah_tebar_awal')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siklus_budidayas');
    }
};