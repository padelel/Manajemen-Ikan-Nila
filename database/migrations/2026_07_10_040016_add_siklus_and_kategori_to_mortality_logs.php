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
        Schema::table('mortality_logs', function (Blueprint $table) {
            $table->foreignId('siklus_budidaya_id')->nullable()->constrained('siklus_budidayas')->nullOnDelete()->after('kolam_id');
            $table->string('kategori_kematian', 50)->nullable()->after('jumlah_mati');
            $table->decimal('berat_total_gram', 8, 2)->nullable()->after('kategori_kematian');
            $table->decimal('ukuran_rata_cm', 5, 1)->nullable()->after('berat_total_gram');
        });
    }

    public function down(): void
    {
        Schema::table('mortality_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('siklus_budidaya_id');
            $table->dropColumn(['kategori_kematian', 'berat_total_gram', 'ukuran_rata_cm']);
        });
    }
};
