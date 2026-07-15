<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\TimelineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_desde_ticket()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'status' => 'open',
            'created_at' => Carbon::now()->subDay(),
        ]);

        $service = new TimelineService();
        $timeline = $service->buildFromTicket($ticket);

        $this->assertArrayHasKey('parent', $timeline);
        $this->assertArrayHasKey('areas', $timeline);
        $this->assertArrayHasKey('sla', $timeline);
        $this->assertNotNull($timeline['parent']['durationFormatted']);
        $this->assertTrue($timeline['parent']['isActive']);
    }

    public function test_timeline_desde_ticket_resuelto()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();

        $ticket = Ticket::factory()->resolved()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'created_at' => Carbon::now()->subDays(2),
            'resolved_at' => Carbon::now()->subDay(),
        ]);

        $service = new TimelineService();
        $timeline = $service->buildFromTicket($ticket);

        $this->assertTrue($timeline['parent']['isCompleted']);
        $this->assertFalse($timeline['parent']['isActive']);
    }

    public function test_timeline_desde_work_order()
    {
        $tecnico = User::factory()->create();
        $cliente = Client::factory()->create();

        $ot = WorkOrder::factory()->completed()->create([
            'technician_id' => $tecnico->id,
            'client_id' => $cliente->id,
            'created_at' => Carbon::now()->subDays(3),
            'completed_date' => Carbon::now()->subDays(2),
        ]);

        $service = new TimelineService();
        $timeline = $service->buildFromWorkOrder($ot);

        $this->assertArrayHasKey('parent', $timeline);
        $this->assertTrue($timeline['parent']['isCompleted']);
        $this->assertNotNull($timeline['parent']['durationFormatted']);
    }

    public function test_timeline_work_order_sin_asignar()
    {
        $cliente = Client::factory()->create();

        $ot = WorkOrder::factory()->create([
            'client_id' => $cliente->id,
            'technician_id' => null,
            'assigned_at' => null,
            'status' => 'pending',
            'created_at' => Carbon::now()->subHours(5),
        ]);

        $service = new TimelineService();
        $timeline = $service->buildFromWorkOrder($ot);

        $this->assertTrue($timeline['parent']['isActive']);
        $this->assertArrayHasKey('parent', $timeline);
        $this->assertNull($timeline['sla']);
        $this->assertNull($timeline['ticket']);
    }
}
