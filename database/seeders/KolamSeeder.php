<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kolam;
use App\Models\User;
use Carbon\Carbon;

class KolamSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data Master Kolam
        $kolam1 = Kolam::create([
            'nama_kolam' => 'Kolam Bioflok Alpha',
            'lokasi' => 'Sektor Timur',
            'panjang_m' => 4.0,
            'lebar_m' => 4.0,
            'kedalaman_m' => 1.5,
            'status_kolam' => 'aktif',
        ]);

        $kolam2 = Kolam::create([
            'nama_kolam' => 'Kolam Bioflok Beta',
            'lokasi' => 'Sektor Barat',
            'panjang_m' => 5.0,
            'lebar_m' => 5.0,
            'kedalaman_m' => 1.2,
            'status_kolam' => 'aktif',
        ]);

        // 2. Ambil data operator yang baru dibuat dari UserSeeder
        $operator = User::where('role', 'operator')->first();

        // 3. Masukkan data ke tabel pivot (operator_kolam)
        if ($operator) {
            $operator->kolams()->attach([
                $kolam1->id => ['tanggal_penugasan' => Carbon::now()->toDateString()],
                $kolam2->id => ['tanggal_penugasan' => Carbon::now()->toDateString()]
            ]);
        }
    }
}