<?php

namespace Database\Factories;

use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductPackaging;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPackagingFactory extends Factory
{
    protected $model = ProductPackaging::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'packaging_type_id' => PackagingType::factory(),
            'name' => fake()->word(),
            'quantity_in_base_unit' => fake()->randomFloat(4, 1, 100),
            'is_default_for_purchase' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attrs) => ['is_default_for_purchase' => true]);
    }
}
