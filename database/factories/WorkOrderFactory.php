<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'technician_id' => User::factory(),
            'client_id' => Client::factory(),
            'ticket_id' => Ticket::factory(),
            'latitude' => fake()->latitude(13.5, 14.0),
            'longitude' => fake()->longitude(-89.5, -88.5),
            'status' => 'pending',
            'scheduled_date' => fake()->date(),
            'completed_date' => null,
            'notes' => fake()->sentence(),
            'service_type' => fake()->randomElement(['internet', 'cable', 'internet_cable']),
            'description' => fake()->paragraph(),
            'code' => fake()->unique()->lexify('OT-#####'),
            'started_at' => null,
            'sla_started_at' => null,
            'accumulated_seconds' => 0,
            'created_by' => User::factory(),
            'wifi_name' => fake()->word(),
            'wifi_password' => fake()->password(),
            'profile_name' => fake()->userName(),
            'profile_password' => fake()->password(),
            'mac' => fake()->macAddress(),
            'pon' => fake()->lexify('PON-??????'),
            'mufa' => fake()->word(),
            'installation_date' => null,
            'assigned_at' => null,
            'assigned_by' => null,
            'requires_noc' => false,
            'zone_id' => Zone::factory(),
            'plan_id' => Plan::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'pending']);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'completed',
            'completed_date' => now(),
        ]);
    }
}
