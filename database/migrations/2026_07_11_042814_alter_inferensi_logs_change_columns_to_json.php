<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inferensi_logs MODIFY kode_diagnosa JSON NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY label_diagnosa JSON NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY kode_kesimpulan JSON NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY tindakan_mitigasi JSON NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY peringatan JSON NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inferensi_logs MODIFY kode_diagnosa VARCHAR(10) NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY label_diagnosa VARCHAR(100) NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY kode_kesimpulan VARCHAR(10) NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY tindakan_mitigasi TEXT NULL');
            DB::statement('ALTER TABLE inferensi_logs MODIFY peringatan TEXT NULL');
        }
    }
};
