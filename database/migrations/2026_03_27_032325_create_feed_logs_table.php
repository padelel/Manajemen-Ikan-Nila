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
        Schema::create('feed_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolam_id')->constrained('kolams')->cascadeOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('rules')->nullOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('tanggal_pakan');
            $table->float('rekomendasi_sistem');
            $table->float('pakan_aktual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_logs');
    }
};
