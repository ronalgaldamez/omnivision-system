<?php

namespace Database\Factories;

use App\Models\MovementType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementTypeFactory extends Factory
{
    protected $model = MovementType::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('type_??'),
            'label' => fake()->word(),
            'icon' => 'circle',
            'color_class' => 'bg-gray-50 text-gray-700',
            'is_active' => true,
        ];
    }
}
