<?php
// database/migrations/xxxx_xx_xx_add_berat_sample_to_parameters_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daily_parameters', function (Blueprint $table) {
            // Tambahkan kolom berat_sample setelah kondisi_visual
            $table->float('berat_sample')->nullable()->after('kondisi_visual');
        });
    }

    public function down()
    {
        Schema::table('daily_parameters', function (Blueprint $table) {
            $table->dropColumn('berat_sample');
        });
    }
};
