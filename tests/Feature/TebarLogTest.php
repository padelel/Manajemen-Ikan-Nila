<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\ParameterHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TebarLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_tebar_updates_kolam_population(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $kolam = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Sungai',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 0,
        ]);

        // Prasyarat: pengecekan air dalam 24 jam terakhir
        ParameterHarian::create([
            'kolam_id' => $kolam->id,
            'user_id' => $user->id,
            'tanggal_cek' => now()->toDateString(),
            'suhu' => 28,
            'ph' => 7.0,
            'do_mgl' => 5.0,
            'amonia_mgl' => 0.01,
            'flok_ml' => 20,

        ]);

        $response = $this
            ->actingAs($user)
            ->post('/tebar', [
                'kolam_id' => $kolam->id,
                'tanggal_tebar' => now()->toDateString(),
                'jumlah_ikan' => 1200,
                'sumber_benih' => 'Supplier X',
            ]);

        $response->assertRedirect('/tebar');

        $this->assertDatabaseHas('kolams', [
            'id' => $kolam->id,
            'jumlah_ikan' => 1200,
        ]);
    }
}
