<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TebarLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_tebar_updates_kolam_average_weight(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $kolam = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Sungai',
            'bentuk_kolam' => 'Persegi',
            'status_kolam' => 'Aktif',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.2,
            'jumlah_ikan' => 0,
            'berat_rata_gram' => 0,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/tebar', [
                'kolam_id' => $kolam->id,
                'tanggal_tebar' => now()->toDateString(),
                'jumlah_ikan' => 1200,
                'berat_rata_gram' => 15.5,
                'sumber_benih' => 'Supplier X',
                'catatan' => 'Tebar awal',
            ]);

        $response->assertRedirect('/tebar');

        $this->assertDatabaseHas('kolams', [
            'id' => $kolam->id,
            'jumlah_ikan' => 1200,
            'berat_rata_gram' => 15.5,
        ]);
    }
}
