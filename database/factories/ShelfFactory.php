<?php

namespace Database\Factories;

use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'code' => fake()->unique()->lexify('SHELF-???'),
            'label' => fake()->word(),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement('rack'),
            'warehouse' => fake()->word(),
            'is_active' => true,
            'is_full' => false,
        ];
    }
}
