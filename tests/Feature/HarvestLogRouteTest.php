<?php

namespace Tests\Feature;

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
}
