<?php

namespace Database\Factories;

use App\Models\PackagingType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackagingTypeFactory extends Factory
{
    protected $model = PackagingType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'unit_of_measure' => fake()->randomElement(['unidad', 'caja', 'paquete', 'rollo', 'metro']),
        ];
    }
}
