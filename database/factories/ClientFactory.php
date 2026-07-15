<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'service' => fake()->randomElement(['internet', 'cable', 'internet_cable']),
            'document_type' => fake()->randomElement(['DUI', 'NIT']),
            'document_number' => fake()->numerify('########-?'),
            'email' => fake()->unique()->safeEmail(),
            'latitude' => fake()->latitude(13.5, 14.0),
            'longitude' => fake()->longitude(-89.5, -88.5),
            'nro_luz' => fake()->numerify('LUZ-####'),
            'installation_address' => fake()->address(),
            'notes' => fake()->sentence(),
            'branch_id' => Branch::factory(),
            'zone_id' => Zone::factory(),
            'plan_id' => Plan::factory(),
            'contract_date' => fake()->date(),
            'departamento' => fake()->randomElement(['San Salvador', 'La Libertad', 'Sonsonate', 'Santa Ana']),
            'municipio' => fake()->city(),
            'distrito' => fake()->city(),
        ];
    }
}
