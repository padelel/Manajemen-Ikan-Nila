<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedLog;
use Carbon\Carbon;

class FeedLogSeeder extends Seeder
{
    public function run(): void
    {
        // Mulai dari 1 Oktober sampai 31 Desember
        $startDate = Carbon::create(2025, 10, 1);
        $endDate = Carbon::create(2025, 12, 31);

        while ($startDate <= $endDate) {
            // Simulasi pakan harian berkisar antara 12 Kg - 15 Kg
            $pakanHarian = rand(120, 150) / 10; 

            FeedLog::create([
                'tanggal_pakan' => $startDate->toDateString(),
                'rekomendasi_sistem' => $pakanHarian,
                'pakan_aktual' => $pakanHarian,
            ]);

            $startDate->addDay();
        }
    }
}