<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductModelFactory extends Factory
{
    protected $model = ProductModel::class;

    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
        ];
    }
}
