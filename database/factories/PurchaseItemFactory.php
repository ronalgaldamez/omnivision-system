<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $unitCost = fake()->randomFloat(4, 1, 100);
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'returned_quantity' => 0,
            'unit_cost' => $unitCost,
            'packaging_id' => null,
            'base_quantity' => $quantity,
            'fractional_quantity' => 0,
            'fractional_units' => 0,
        ];
    }
}
