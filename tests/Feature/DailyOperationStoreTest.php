<?php

namespace Tests\Feature;

use App\Models\DailyParameter;
use App\Models\FeedLog;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Kolam;
use App\Models\MortalityLog;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyOperationStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_operation_stores_mortality_correctly(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $kolam = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Sungai',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 1000,
            'berat_rata_gram' => 50,
        ]);

        $rule = Rule::create([
            'kode_rule' => 'R01',
            'kondisi_suhu' => '25-32',
            'kondisi_ph' => '6.5-8.5',
            'kondisi_visual' => 'Jernih',
            'rekomendasi_dosis_persen' => 100,
        ]);

        $inventory = Inventory::create([
            'nama_pakan' => 'Pakan Premium',
            'total_stok_kg' => 100,
            'terakhir_update' => now()->toDateString(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('operasi.store'), [
                'kolam_id' => $kolam->id,
                'jumlah_mati' => 5,
                'catatan_kematian' => 'Predator',
                'suhu' => 28,
                'ph' => 7.5,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => 50,
                'frekuensi' => 2,
                'rule_id' => $rule->id,
                'rekomendasi_sistem' => 2.5,
                'pakan_aktual' => 2.5,
                'feeds' => [
                    [
                        'inventory_id' => $inventory->id,
                        'rasio' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(route('feedlog.index'));

        // Verify MortalityLog was saved with correct field names
        $this->assertDatabaseHas('mortality_logs', [
            'kolam_id' => $kolam->id,
            'user_id' => $user->id,
            'jumlah_mati' => 5,
            'catatan' => 'Predator',
        ]);

        // Verify DailyParameter was saved
        $this->assertDatabaseHas('daily_parameters', [
            'kolam_id' => $kolam->id,
            'user_id' => $user->id,
            'suhu' => 28,
            'ph' => 7.5,
            'kondisi_visual' => 'Jernih',
            'berat_sample' => 50,
        ]);

        // Verify FeedLog was saved
        $this->assertDatabaseHas('feed_logs', [
            'kolam_id' => $kolam->id,
            'user_id' => $user->id,
            'frekuensi' => 2,
            'rule_id' => $rule->id,
            'pakan_aktual' => 2.5,
        ]);

        // Verify FeedLogDetail was saved
        $this->assertDatabaseHas('feed_log_details', [
            'inventory_id' => $inventory->id,
            'rasio' => 1,
            'jumlah_kg' => 2.5,
        ]);

        // Verify Inventory stock was reduced
        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'total_stok_kg' => 97.5,
        ]);

        // Verify InventoryLog was created
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'tipe' => 'Keluar',
            'jumlah' => 2.5,
        ]);

        // Verify Kolam fish count was reduced
        $this->assertDatabaseHas('kolams', [
            'id' => $kolam->id,
            'jumlah_ikan' => 995,
        ]);
    }

    public function test_daily_operation_without_mortality(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $kolam = Kolam::create([
            'nama_kolam' => 'Kolam B',
            'lokasi' => 'Sungai',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 1000,
            'berat_rata_gram' => 50,
        ]);

        $rule = Rule::create([
            'kode_rule' => 'R01',
            'kondisi_suhu' => '25-32',
            'kondisi_ph' => '6.5-8.5',
            'kondisi_visual' => 'Jernih',
            'rekomendasi_dosis_persen' => 100,
        ]);

        $inventory = Inventory::create([
            'nama_pakan' => 'Pakan Premium',
            'total_stok_kg' => 100,
            'terakhir_update' => now()->toDateString(),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('operasi.store'), [
                'kolam_id' => $kolam->id,
                'jumlah_mati' => 0,
                'catatan_kematian' => 'Aman',
                'suhu' => 28,
                'ph' => 7.5,
                'kondisi_visual' => 'Jernih',
                'berat_sample' => 50,
                'frekuensi' => 2,
                'rule_id' => $rule->id,
                'rekomendasi_sistem' => 2.5,
                'pakan_aktual' => 2.5,
                'feeds' => [
                    [
                        'inventory_id' => $inventory->id,
                        'rasio' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(route('feedlog.index'));

        // Verify no MortalityLog was created when jumlah_mati = 0
        $this->assertDatabaseMissing('mortality_logs', [
            'kolam_id' => $kolam->id,
            'jumlah_mati' => 0,
        ]);

        // Verify Kolam fish count remains unchanged
        $this->assertDatabaseHas('kolams', [
            'id' => $kolam->id,
            'jumlah_ikan' => 1000,
        ]);
    }
}
