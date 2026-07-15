<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'contact_name' => fake()->name(),
            'phones' => [fake()->phoneNumber()],
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'nrc' => fake()->numerify('######-?'),
            'nit' => fake()->numerify('####-######-###-?'),
            'bank_accounts' => [['bank' => fake()->company(), 'account' => fake()->bankAccountNumber()]],
        ];
    }
}
