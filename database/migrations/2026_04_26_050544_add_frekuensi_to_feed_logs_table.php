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
        Schema::table('feed_logs', function (Blueprint $table) {
            // Menambahkan kolom frekuensi setelah kolom tanggal_pakan atau pakan_aktual
            $table->integer('frekuensi')->nullable()->after('tanggal_pakan')->comment('Berapa kali pakan diberikan (misal: 2 atau 3)');
        });
    }

    public function down(): void
    {
        Schema::table('feed_logs', function (Blueprint $table) {
            $table->dropColumn('frekuensi');
        });
    }
};
