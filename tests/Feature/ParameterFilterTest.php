<?php

namespace Tests\Feature;

use App\Models\Kolam;
use App\Models\ParameterHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParameterFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_parameter_index_can_filter_by_kolam_and_date_range(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        $kolamA = Kolam::create([
            'nama_kolam' => 'Kolam A',
            'lokasi' => 'Lahan Timur',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'kedalaman_m' => 1.5,
        ]);

        $kolamB = Kolam::create([
            'nama_kolam' => 'Kolam B',
            'lokasi' => 'Lahan Barat',
            'bentuk_kolam' => 'Persegi',
            'panjang_m' => 12,
            'lebar_m' => 6,
            'kedalaman_m' => 1.8,
        ]);

        ParameterHarian::create([
            'kolam_id' => $kolamA->id,
            'user_id' => $user->id,
            'tanggal_cek' => '2026-07-01',
            'suhu' => 27.5,
            'ph' => 7.2,
            'do_mgl' => 5.5,
            'amonia_mgl' => 0.01,
            'flok_ml' => 25.0,
        ]);

        ParameterHarian::create([
            'kolam_id' => $kolamB->id,
            'user_id' => $user->id,
            'tanggal_cek' => '2026-07-10',
            'suhu' => 28.0,
            'ph' => 7.4,
            'do_mgl' => 6.0,
            'amonia_mgl' => 0.00,
            'flok_ml' => 28.0,
        ]);

        $response = $this
            ->actingAs($user)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get(route('parameter.index', [
                'kolam_id' => $kolamB->id,
                'start_date' => '2026-07-05',
                'end_date' => '2026-07-15',
            ]));

        $response->assertStatus(200);
        $response->assertJsonPath('props.riwayat.0.kolam_id', $kolamB->id);
        $response->assertJsonPath('props.filters.kolam_id', (string) $kolamB->id);
        $response->assertJsonPath('props.filters.start_date', '2026-07-05');
        $response->assertJsonPath('props.filters.end_date', '2026-07-15');
        $this->assertCount(1, $response->json('props.riwayat'));
    }
}
