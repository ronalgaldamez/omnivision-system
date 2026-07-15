<?php

namespace Tests\Feature;

use App\Models\SlaGoal;
use App\Models\Ticket;
use App\Models\User;
use App\Models\ServiceType;
use App\Models\Client;
use App\Services\SlaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoSLATest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_recibe_sla_y_se_evalua_cumplido()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();
        $serviceType = ServiceType::factory()->create(['name' => 'internet']);

        SlaGoal::factory()->create([
            'priority' => 'high',
            'service_type_id' => $serviceType->id,
            'minutes' => 120,
            'is_active' => true,
        ]);

        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'service_type' => 'internet',
            'priority' => 'high',
            'status' => 'open',
            'created_at' => Carbon::now()->subHour(),
        ]);

        $slaService = new SlaService();
        $slaService->assignSlaToTicket($ticket);

        $ticket->refresh();

        $this->assertNotNull($ticket->sla_goal_id);
        $this->assertNotNull($ticket->sla_deadline_at);
        $this->assertTrue($ticket->sla_deadline_at->gt($ticket->created_at));

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => Carbon::now()->subMinutes(30),
        ]);

        $slaService->evaluateSla($ticket);

        $ticket->refresh();

        $this->assertTrue($ticket->sla_met);
        $this->assertNotNull($ticket->sla_evaluated_at);
    }

    public function test_ticket_excede_sla()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();

        SlaGoal::factory()->create([
            'priority' => 'critical',
            'service_type_id' => null,
            'minutes' => 60,
            'is_active' => true,
        ]);

        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'priority' => 'critical',
            'status' => 'open',
            'created_at' => Carbon::now()->subHours(3),
        ]);

        $slaService = new SlaService();
        $slaService->assignSlaToTicket($ticket, $ticket->created_at);

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => Carbon::now()->subMinute(),
        ]);

        $slaService->evaluateSla($ticket);
        $ticket->refresh();

        $this->assertFalse($ticket->sla_met);
    }

    public function test_sla_tickets_en_riesgo_y_vencidos()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();

        SlaGoal::factory()->create([
            'priority' => 'medium',
            'service_type_id' => null,
            'minutes' => 60,
            'is_active' => true,
        ]);

        $enRiesgo = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => Carbon::now()->subMinutes(35),
        ]);

        $vencido = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'priority' => 'medium',
            'status' => 'open',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        $slaService = new SlaService();
        $slaService->assignSlaToTicket($enRiesgo, $enRiesgo->created_at);
        $slaService->assignSlaToTicket($vencido, $vencido->created_at);

        $atRisk = $slaService->getAtRiskTickets(30);
        $overdue = $slaService->getOverdueTickets();

        $this->assertCount(1, $atRisk);
        $this->assertCount(1, $overdue);
    }
}
