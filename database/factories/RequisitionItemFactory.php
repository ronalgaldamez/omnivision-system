<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequisitionItemFactory extends Factory
{
    protected $model = RequisitionItem::class;

    public function definition(): array
    {
        return [
            'requisition_id' => Requisition::factory(),
            'product_id' => Product::factory(),
            'quantity_requested' => fake()->randomFloat(2, 1, 20),
            'quantity_used' => fake()->randomFloat(2, 0, 20),
        ];
    }
}
