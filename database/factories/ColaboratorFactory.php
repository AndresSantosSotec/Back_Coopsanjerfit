<?php

namespace Database\Factories;

use App\Models\Colaborator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColaboratorFactory extends Factory
{
    protected $model = Colaborator::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nombre'  => $this->faker->name,
            'nivel_asignado' => $this->faker->randomElement(['KoalaFit', 'JaguarFit', 'HalconFit']),
        ];
    }
}

