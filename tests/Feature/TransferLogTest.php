<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_transfer_updates_target_average_weight(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $kolamAsal = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Sawah',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.0,
            'jumlah_ikan' => 1000,
            'berat_rata_gram' => 15.0,
        ]);

        $kolamTujuan = Kolam::create([
            'nama_kolam' => 'Kolam B',
            'lokasi' => 'Sawah',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 8,
            'lebar_m' => 4,
            'kedalaman_m' => 1.0,
            'jumlah_ikan' => 200,
            'berat_rata_gram' => 20.0,
        ]);

        $response = $this->actingAs($user)->post('/transfer', [
            'kolam_asal_id' => $kolamAsal->id,
            'kolam_tujuan_id' => $kolamTujuan->id,
            'jumlah_ikan' => 500,
            'tanggal_transfer' => now()->toDateString(),
            'catatan' => 'Transfer parsial untuk sorting.',
        ]);

        $response->assertRedirect('/transfer');

        $this->assertDatabaseHas('kolams', [
            'id' => $kolamAsal->id,
            'jumlah_ikan' => 500,
            'berat_rata_gram' => 15.0,
        ]);

        $expectedAverage = round((200 * 20.0 + 500 * 15.0) / 700, 2);
        $this->assertDatabaseHas('kolams', [
            'id' => $kolamTujuan->id,
            'jumlah_ikan' => 700,
            'berat_rata_gram' => $expectedAverage,
        ]);
    }
}
