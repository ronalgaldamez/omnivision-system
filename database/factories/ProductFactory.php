<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' ' . fake()->randomNumber(3),
            'description' => fake()->sentence(),
            'sku' => null,
            'current_stock' => fake()->numberBetween(0, 100),
            'stock_min' => fake()->numberBetween(1, 10),
            'stock_max' => fake()->numberBetween(50, 200),
            'unit_of_measure' => fake()->randomElement(['unidad', 'caja', 'metro', 'litro']),
            'measure_value' => fake()->randomFloat(2, 1, 10),
            'brand_id' => Brand::factory(),
            'model_id' => ProductModel::factory(),
            'category_id' => Category::factory(),
            'average_cost' => fake()->randomFloat(4, 1, 100),
            'total_value' => fake()->randomFloat(2, 100, 10000),
            'is_obsolete' => false,
            'is_floating' => false,
            'base_unit' => fake()->randomElement(['unidad', 'caja']),
        ];
    }

    public function obsolete(): static
    {
        return $this->state(fn (array $attrs) => ['is_obsolete' => true]);
    }

    public function floating(): static
    {
        return $this->state(fn (array $attrs) => ['is_floating' => true]);
    }
}
