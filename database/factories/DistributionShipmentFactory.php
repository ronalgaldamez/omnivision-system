<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\DistributionShipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionShipmentFactory extends Factory
{
    protected $model = DistributionShipment::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('ENV-#####'),
            'branch_id' => Branch::factory(),
            'status' => 'pending',
            'created_by' => User::factory(),
            'confirmed_by' => null,
            'in_transit_at' => null,
            'delivered_at' => null,
            'confirmed_at' => null,
            'notes' => fake()->sentence(),
        ];
    }

    public function inTransit(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'in_transit',
            'in_transit_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'delivered',
            'in_transit_at' => now()->subDay(),
            'delivered_at' => now(),
        ]);
    }
}
