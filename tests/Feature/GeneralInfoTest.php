<?php

namespace Tests\Feature;

use App\Models\GeneralInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GeneralInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_general_info()
    {
        $user = User::factory()->create();
        GeneralInfo::factory()->create(['title' => 'Guide']);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/app/info');
        $response->assertStatus(200)->assertJsonFragment(['title' => 'Guide']);
    }
}
