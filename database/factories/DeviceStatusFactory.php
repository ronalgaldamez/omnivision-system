<?php

namespace Database\Factories;

use App\Models\DeviceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceStatusFactory extends Factory
{
    protected $model = DeviceStatus::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('status_??'),
            'name' => fake()->word(),
            'color_class' => fake()->randomElement(['bg-green-100 text-green-800', 'bg-yellow-100 text-yellow-800', 'bg-red-100 text-red-800']),
            'is_active' => true,
        ];
    }
}
