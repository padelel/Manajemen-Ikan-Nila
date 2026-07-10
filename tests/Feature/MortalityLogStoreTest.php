<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\SiklusBudidaya;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MortalityLogStoreTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private Kolam $kolam;

    private SiklusBudidaya $siklus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create(['role' => 'operator']);

        $this->kolam = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Sawah',
            'status_kolam' => 'aktif',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 1000,
            'berat_rata_gram' => 50.0,
        ]);

        $this->siklus = SiklusBudidaya::create([
            'kolam_id' => $this->kolam->id,
            'tanggal_mulai' => now()->subDays(30),
            'jumlah_tebar_awal' => 1000,
            'status_aktif' => 'berjalan',
        ]);
    }

    public function test_store_mortality_with_category(): void
    {
        $response = $this
            ->actingAs($this->operator)
            ->post(route('kematian.store'), [
                'kolam_id' => $this->kolam->id,
                'siklus_budidaya_id' => $this->siklus->id,
                'tanggal_kematian' => now()->toDateString(),
                'jumlah_mati' => 10,
                'kategori_kematian' => 'penyakit',
                'catatan' => 'Ditemukan mengambang',
            ]);

        $response->assertRedirect(route('kematian.index'));

        $this->assertDatabaseHas('mortality_logs', [
            'kolam_id' => $this->kolam->id,
            'siklus_budidaya_id' => $this->siklus->id,
            'user_id' => $this->operator->id,
            'jumlah_mati' => 10,
            'kategori_kematian' => 'penyakit',
            'catatan' => 'Ditemukan mengambang',
        ]);

        $this->assertDatabaseHas('kolams', [
            'id' => $this->kolam->id,
            'jumlah_ikan' => 990,
        ]);
    }

    public function test_store_mortality_without_optional_fields(): void
    {
        $response = $this
            ->actingAs($this->operator)
            ->post(route('kematian.store'), [
                'kolam_id' => $this->kolam->id,
                'siklus_budidaya_id' => $this->siklus->id,
                'tanggal_kematian' => now()->toDateString(),
                'jumlah_mati' => 5,
            ]);

        $response->assertRedirect(route('kematian.index'));

        $this->assertDatabaseHas('mortality_logs', [
            'kolam_id' => $this->kolam->id,
            'jumlah_mati' => 5,
            'kategori_kematian' => null,
            'berat_total_gram' => null,
            'ukuran_rata_cm' => null,
        ]);
    }

    public function test_store_mortality_reduces_kolam_population(): void
    {
        $this->actingAs($this->operator)->post(route('kematian.store'), [
            'kolam_id' => $this->kolam->id,
            'siklus_budidaya_id' => $this->siklus->id,
            'tanggal_kematian' => now()->toDateString(),
            'jumlah_mati' => 50,
        ]);

        $this->assertDatabaseHas('kolams', [
            'id' => $this->kolam->id,
            'jumlah_ikan' => 950,
        ]);

        $this->actingAs($this->operator)->post(route('kematian.store'), [
            'kolam_id' => $this->kolam->id,
            'siklus_budidaya_id' => $this->siklus->id,
            'tanggal_kematian' => now()->toDateString(),
            'jumlah_mati' => 30,
        ]);

        $this->assertDatabaseHas('kolams', [
            'id' => $this->kolam->id,
            'jumlah_ikan' => 920,
        ]);
    }

    public function test_store_mortality_rejects_exceeding_population(): void
    {
        $response = $this
            ->actingAs($this->operator)
            ->post(route('kematian.store'), [
                'kolam_id' => $this->kolam->id,
                'siklus_budidaya_id' => $this->siklus->id,
                'tanggal_kematian' => now()->toDateString(),
                'jumlah_mati' => 9999,
            ]);

        $response->assertSessionHasErrors('jumlah_mati');

        $this->assertDatabaseMissing('mortality_logs', [
            'jumlah_mati' => 9999,
        ]);
    }

    public function test_store_mortality_rejects_invalid_siklus(): void
    {
        $completedSiklus = SiklusBudidaya::create([
            'kolam_id' => $this->kolam->id,
            'tanggal_mulai' => now()->subDays(60),
            'jumlah_tebar_awal' => 500,
            'status_aktif' => 'selesai',
        ]);

        $response = $this
            ->actingAs($this->operator)
            ->post(route('kematian.store'), [
                'kolam_id' => $this->kolam->id,
                'siklus_budidaya_id' => $completedSiklus->id,
                'tanggal_kematian' => now()->toDateString(),
                'jumlah_mati' => 10,
            ]);

        $response->assertSessionHasErrors('siklus_budidaya_id');
    }

    public function test_store_mortality_rejects_siklus_from_different_kolam(): void
    {
        $otherKolam = Kolam::create([
            'nama_kolam' => 'Kolam B',
            'lokasi' => 'Gedung',
            'status_kolam' => 'aktif',
            'panjang_m' => 8,
            'lebar_m' => 4,
            'kedalaman_m' => 1.0,
            'jumlah_ikan' => 500,
        ]);

        $otherSiklus = SiklusBudidaya::create([
            'kolam_id' => $otherKolam->id,
            'tanggal_mulai' => now()->subDays(20),
            'jumlah_tebar_awal' => 500,
            'status_aktif' => 'berjalan',
        ]);

        $response = $this
            ->actingAs($this->operator)
            ->post(route('kematian.store'), [
                'kolam_id' => $this->kolam->id,
                'siklus_budidaya_id' => $otherSiklus->id,
                'tanggal_kematian' => now()->toDateString(),
                'jumlah_mati' => 10,
            ]);

        $response->assertSessionHasErrors('siklus_budidaya_id');
    }

    public function test_create_page_returns_operator_assigned_kolams(): void
    {
        $kolamTanpaSiklus = Kolam::create([
            'nama_kolam' => 'Kolam Tanpa Siklus',
            'lokasi' => 'Gudang',
            'status_kolam' => 'tidak aktif',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.0,
            'jumlah_ikan' => 0,
        ]);

        $this->operator->kolams()->attach($this->kolam->id, ['tanggal_penugasan' => now()]);

        $response = $this
            ->actingAs($this->operator)
            ->get(route('kematian.create'));

        $response->assertInertia(fn ($page) => $page
            ->component('Mortality/Create')
            ->has('kolams', 1)
            ->where('kolams.0.id', $this->kolam->id)
        );
    }
}
