<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchInventoryFactory extends Factory
{
    protected $model = BranchInventory::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'product_id' => Product::factory(),
            'allocated_quantity' => fake()->randomFloat(4, 0, 100),
        ];
    }
}
