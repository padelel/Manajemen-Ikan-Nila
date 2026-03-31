<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tabel Induk (Tidak punya Foreign Key)
        $this->call([
            UserSeeder::class,
            RuleSeeder::class,
            InventorySeeder::class,
            KolamSeeder::class,
        ]);

        // 2. Tabel Anak (Punya Foreign Key, butuh data dari tabel di atas)
        $this->call([
            DailyParameterSeeder::class,
            MortalityLogSeeder::class,
            FeedLogSeeder::class,
        ]);
    }
}