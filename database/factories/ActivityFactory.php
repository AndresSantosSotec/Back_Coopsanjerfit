<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exercise_type' => 'walk',
            'duration' => 10,
            'duration_unit' => 'minutos',
            'steps' => 0,
            'is_valid' => true,
        ];
    }
}

