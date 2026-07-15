<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPhone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientPhoneFactory extends Factory
{
    protected $model = ClientPhone::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'number' => fake()->unique()->phoneNumber(),
            'type' => fake()->randomElement(['mobile', 'home', 'work']),
        ];
    }
}
