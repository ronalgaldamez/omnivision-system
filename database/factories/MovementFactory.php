<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(['entry', 'exit']),
            'quantity' => fake()->numberBetween(1, 50),
            'unit_cost' => fake()->randomFloat(4, 1, 100),
            'description' => fake()->sentence(),
            'user_id' => User::factory(),
            'reference_type' => null,
            'reference_id' => null,
            'total_value' => fake()->randomFloat(2, 10, 5000),
            'branch_id' => null,
        ];
    }

    public function entry(): static
    {
        return $this->state(fn (array $attrs) => ['type' => 'entry']);
    }

    public function exit(): static
    {
        return $this->state(fn (array $attrs) => ['type' => 'exit']);
    }
}
