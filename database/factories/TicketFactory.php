<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Plan;
use App\Models\SlaGoal;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'description' => fake()->paragraph(),
            'service_type' => fake()->randomElement(['internet', 'cable', 'both']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'origin' => fake()->randomElement(['call', 'email', 'whatsapp', 'onsite']),
            'requires_noc' => false,
            'status' => 'open',
            'created_by' => User::factory(),
            'resolved_by' => null,
            'resolved_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'ticket_code' => 'TKT-' . fake()->unique()->randomNumber(5),
            'zone_id' => Zone::factory(),
            'plan_id' => Plan::factory(),
            'started_at' => null,
            'l1_ended_at' => null,
            'escalated_at' => null,
            'l2_started_at' => null,
            'l2_ended_at' => null,
            'sla_goal_id' => null,
            'sla_deadline_at' => null,
            'sla_met' => null,
            'sla_evaluated_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'open']);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => User::factory(),
        ]);
    }
}
