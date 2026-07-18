<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'requires_noc' => fake()->boolean(),
            'requires_ot' => fake()->boolean(),
            'requires_contract' => fake()->boolean(),
        ];
    }
}
