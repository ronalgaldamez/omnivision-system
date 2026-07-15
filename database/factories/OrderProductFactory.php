<?php

namespace Database\Factories;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'unit_cost_at_time' => fake()->randomFloat(4, 1, 100),
        ];
    }
}
