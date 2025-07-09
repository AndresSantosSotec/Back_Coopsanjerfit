<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Colaborator;
use App\Services\FitcoinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitcoinServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_reward_respects_daily_limit_and_meta()
    {
        $service = new FitcoinService();
        $colaborator = Colaborator::factory()->create(['nivel_asignado' => 'KoalaFit']);

        // Activity below meta but with evidencia
        $activity = Activity::factory()->create([
            'user_id' => $colaborator->user_id,
            'duration' => 1,
            'duration_unit' => 'minutos',
            'selfie_path' => 'selfie.jpg',
            'location_lat' => 1.0,
        ]);

        $reward = $service->calculateActivityReward($activity, $colaborator);

        $this->assertEquals(1, $reward);

      

        // Award the user to almost reach the limit
        $service->award($colaborator, 9, 'setup');

        // Another valid activity that meets meta
        $activity2 = Activity::factory()->create([
            'user_id' => $colaborator->user_id,
            'duration' => 30,
            'duration_unit' => 'minutos',
            'steps' => 4000,
            'selfie_path' => 'selfie2.jpg',
            'location_lat' => 1.0,
        ]);

        $reward2 = $service->calculateActivityReward($activity2, $colaborator);
        // Only one CoinFit should be left to reach the daily limit
        $this->assertEquals(1, $reward2);
    }

    public function test_level_name_is_normalized()
    {
        $service = new FitcoinService();
        $col = Colaborator::factory()->create(['nivel_asignado' => ' Halcón ']);

        $activity = Activity::factory()->create([
            'user_id' => $col->user_id,
            'duration' => 1,
            'duration_unit' => 'minutos',
            'selfie_path' => 'a.jpg',
            'location_lat' => 1.0,
        ]);


        // Meta no cumplida, debería otorgar sólo 1 por ser actividad corta
        $reward = $service->calculateActivityReward($activity, $col);
        $this->assertEquals(1, $reward);

    }
}
