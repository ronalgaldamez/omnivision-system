<?php

namespace Database\Factories;

use App\Models\ServiceType;
use App\Models\SlaGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlaGoalFactory extends Factory
{
    protected $model = SlaGoal::class;

    public function definition(): array
    {
        return [
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'service_type_id' => ServiceType::factory(),
            'minutes' => fake()->numberBetween(30, 480),
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }
}
