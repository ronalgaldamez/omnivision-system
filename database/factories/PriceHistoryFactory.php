<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PriceHistory;
use App\Models\User;
use App\Models\Zone;
use App\Models\ZonePlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceHistoryFactory extends Factory
{
    protected $model = PriceHistory::class;

    public function definition(): array
    {
        return [
            'zone_plan_price_id' => ZonePlanPrice::factory(),
            'plan_id' => Plan::factory(),
            'zone_id' => Zone::factory(),
            'old_price' => fake()->randomFloat(2, 10, 100),
            'new_price' => fake()->randomFloat(2, 10, 100),
            'reason' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
