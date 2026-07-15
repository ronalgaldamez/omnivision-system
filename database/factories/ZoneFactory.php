<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->city(),
            'level' => fake()->randomElement(['departamento', 'municipio', 'localidad']),
            'has_internet' => fake()->boolean(),
            'has_cable' => fake()->boolean(),
            'is_active' => true,
        ];
    }

    public function childOf(Zone $parent): static
    {
        return $this->state(fn (array $attrs) => [
            'parent_id' => $parent->id,
            'branch_id' => $parent->branch_id,
            'level' => $parent->level + 1,
        ]);
    }
}
