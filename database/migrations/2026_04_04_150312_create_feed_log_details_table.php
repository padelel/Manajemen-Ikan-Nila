<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Hapus inventory_id dari feed_logs (jika sebelumnya ada)
        Schema::table('feed_logs', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropColumn('inventory_id');
        });

        // 2. Buat tabel detail untuk multi-pakan
        Schema::create('feed_log_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained();
            $table->integer('rasio')->default(1);
            $table->float('jumlah_kg');
            $table->timestamps();
        });
    }
};
