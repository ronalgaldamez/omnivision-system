<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->unique()->company().' S.A. de C.V.',
            'nombre_comercial' => fake()->company(),
            'tipo' => 'sociedad',
            'nit' => fake()->numerify('####-######-###-#'),
            'is_active' => true,
        ];
    }
}
