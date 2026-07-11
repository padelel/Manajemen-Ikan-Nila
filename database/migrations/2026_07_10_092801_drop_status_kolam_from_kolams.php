<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kolams', function (Blueprint $table) {
            $table->dropColumn('status_kolam');
        });
    }

    public function down(): void
    {
        Schema::table('kolams', function (Blueprint $table) {
            $table->enum('status_kolam', ['aktif', 'tidak aktif', 'maintenance'])->default('tidak aktif');
        });
    }
};
