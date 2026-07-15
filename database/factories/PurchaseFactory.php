<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $iva = round($subtotal * 0.13, 2);
        return [
            'supplier_id' => Supplier::factory(),
            'invoice_number' => fake()->unique()->numerify('FAC-####'),
            'purchase_date' => fake()->date(),
            'notes' => fake()->sentence(),
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'iva_amount' => $iva,
            'total' => $subtotal + $iva,
            'include_iva' => true,
        ];
    }
}
