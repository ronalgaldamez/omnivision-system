<?php

namespace Database\Factories;

use App\Models\PlanGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanGroupFactory extends Factory
{
    protected $model = PlanGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
