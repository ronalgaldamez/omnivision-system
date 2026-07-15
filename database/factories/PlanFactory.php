<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'service_type' => fake()->randomElement(['internet', 'cable', 'internet_cable']),
            'base_price' => fake()->randomFloat(2, 10, 100),
            'speed' => fake()->randomElement(['10Mbps', '20Mbps', '50Mbps', '100Mbps']),
            'channels' => fake()->randomElement(['60', '120', '200']),
            'is_active' => true,
        ];
    }
}
