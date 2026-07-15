<?php

namespace Database\Factories;

use App\Models\DistributionShipment;
use App\Models\DistributionShipmentItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionShipmentItemFactory extends Factory
{
    protected $model = DistributionShipmentItem::class;

    public function definition(): array
    {
        return [
            'shipment_id' => DistributionShipment::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->word(),
            'quantity' => fake()->randomFloat(4, 1, 50),
        ];
    }
}
