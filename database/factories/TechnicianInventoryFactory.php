<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\TechnicianInventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicianInventoryFactory extends Factory
{
    protected $model = TechnicianInventory::class;

    public function definition(): array
    {
        return [
            'technician_id' => User::factory(),
            'product_id' => Product::factory(),
            'quantity_in_hand' => fake()->randomFloat(2, 0, 50),
        ];
    }
}
