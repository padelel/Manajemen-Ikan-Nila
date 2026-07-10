<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestLogRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_store_full_harvest_with_sr_calculation(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $kolam = Kolam::create(['nama_kolam' => 'Kolam A', 'lokasi' => 'Desa', 'panjang_m' => 10, 'lebar_m' => 5, 'kedalaman_m' => 1.5, 'status_kolam' => 'aktif', 'jumlah_ikan' => 950]);
        $siklus = SiklusBudidaya::create([
            'kolam_id' => $kolam->id,
            'tanggal_mulai' => now()->subWeeks(4)->toDateString(),
            'jumlah_tebar_awal' => 1000,
            'status_aktif' => 'berjalan',
        ]);

        MortalityLog::create([
            'user_id' => $user->id,
            'kolam_id' => $kolam->id,
            'siklus_budidaya_id' => $siklus->id,
            'tanggal_kematian' => now()->subDays(3)->toDateString(),
            'jumlah_mati' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('panen.store', $siklus->id), [
            'tanggal_panen' => now()->toDateString(),
            'catatan' => 'Panen akhir siklus.',
        ]);

        $response->assertRedirect(route('panen.index'));
        $this->assertDatabaseHas('harvest_logs', [
            'kolam_id' => $kolam->id,
            'siklus_budidaya_id' => $siklus->id,
            'jenis_panen' => 'total',
            'jumlah_ikan_panen' => 950,
            'survival_rate' => 95.0,
        ]);

        $this->assertDatabaseHas('siklus_budidayas', [
            'id' => $siklus->id,
            'status_aktif' => 'selesai',
        ]);
    }

    public function test_harvest_create_shows_population_and_sr(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $kolam = Kolam::create(['nama_kolam' => 'Kolam A', 'lokasi' => 'Desa', 'panjang_m' => 10, 'lebar_m' => 5, 'kedalaman_m' => 1.5, 'status_kolam' => 'aktif', 'jumlah_ikan' => 900]);
        $siklus = SiklusBudidaya::create([
            'kolam_id' => $kolam->id,
            'tanggal_mulai' => now()->subWeeks(4)->toDateString(),
            'jumlah_tebar_awal' => 1000,
            'status_aktif' => 'berjalan',
        ]);

        MortalityLog::create([
            'user_id' => $user->id,
            'kolam_id' => $kolam->id,
            'siklus_budidaya_id' => $siklus->id,
            'tanggal_kematian' => now()->subDays(3)->toDateString(),
            'jumlah_mati' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('panen.create', $siklus->id));

        $response->assertInertia(fn ($page) => $page
            ->component('HarvestLog/Create')
            ->where('siklus.populasi_panen', 900)
            ->where('siklus.total_mati', 100)
            ->where('siklus.sr', 90)
        );
    }
}
