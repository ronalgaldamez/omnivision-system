<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TechnicianReturn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicianReturnFactory extends Factory
{
    protected $model = TechnicianReturn::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->randomFloat(2, 1, 20),
            'type' => fake()->randomElement(['damage', 'return']),
            'notes' => fake()->sentence(),
        ];
    }
}
