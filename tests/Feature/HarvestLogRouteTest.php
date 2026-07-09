<?php

namespace Tests\Feature;

use App\Models\HarvestLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\SiklusBudidaya;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestLogRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_access_panen_create_page(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($user)->get('/panen/create');

        $response->assertOk();
    }

    public function test_operator_can_store_full_harvest_with_current_population(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $kolam = Kolam::create(['nama_kolam' => 'Kolam A', 'lokasi' => 'Desa', 'panjang_m' => 10, 'lebar_m' => 5, 'kedalaman_m' => 1.5, 'status_kolam' => 'aktif']);
        $siklus = SiklusBudidaya::create([
            'kolam_id' => $kolam->id,
            'tanggal_mulai' => now()->subWeeks(4)->toDateString(),
            'jumlah_tebar_awal' => 1000,
            'status_aktif' => 'berjalan',
        ]);

        MortalityLog::create([
            'user_id' => $user->id,
            'kolam_id' => $kolam->id,
            'tanggal_kematian' => now()->subDays(3)->toDateString(),
            'jumlah_mati' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('panen.store'), [
            'kolam_id' => $kolam->id,
            'jenis_panen' => 'Full',
            'tanggal_panen' => now()->toDateString(),
            'jumlah_ikan' => 950,
            'berat_total_kg' => 120.5,
            'catatan' => 'Panen akhir siklus full.',
        ]);

        $response->assertRedirect(route('panen.index'));
        $this->assertDatabaseHas('harvest_logs', [
            'kolam_id' => $kolam->id,
            'siklus_budidaya_id' => $siklus->id,
            'jenis_panen' => 'total',
            'jumlah_ikan_panen' => 950,
            'berat_total_kg' => 120.5,
        ]);

        $this->assertDatabaseHas('siklus_budidayas', [
            'id' => $siklus->id,
            'status_aktif' => 'selesai',
        ]);
    }

    public function test_operator_can_view_panen_detail_page(): void
    {
        $user = User::factory()->create(['role' => 'operator']);
        $kolam = Kolam::create(['nama_kolam' => 'Kolam A', 'lokasi' => 'Desa', 'panjang_m' => 10, 'lebar_m' => 5, 'kedalaman_m' => 1.5, 'status_kolam' => 'aktif']);
        $siklus = SiklusBudidaya::create(['kolam_id' => $kolam->id, 'tanggal_mulai' => now()->subWeeks(4)->toDateString(), 'jumlah_tebar_awal' => 1000, 'status_aktif' => 'berjalan']);
        $panen = HarvestLog::create([
            'kolam_id' => $kolam->id,
            'siklus_budidaya_id' => $siklus->id,
            'user_id' => $user->id,
            'jenis_panen' => 'total',
            'tanggal_panen' => now()->toDateString(),
            'jumlah_ikan_panen' => 950,
            'berat_total_kg' => 150.50,
            'survival_rate' => 95.0,
            'catatan' => 'Panen akhir siklus',
        ]);

        $response = $this->actingAs($user)->get(route('panen.show', $panen->id));

        $response->assertOk();
    }
}
