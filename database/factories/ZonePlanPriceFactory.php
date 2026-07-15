<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Zone;
use App\Models\ZonePlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZonePlanPriceFactory extends Factory
{
    protected $model = ZonePlanPrice::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'plan_id' => Plan::factory(),
            'price' => fake()->randomFloat(2, 10, 200),
        ];
    }
}
