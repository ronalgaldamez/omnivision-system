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

    public function test_timeline_multi_ot_muestra_fases_por_separado()
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();
        $sup1 = User::factory()->create();
        $sup2 = User::factory()->create();
        $tec1 = User::factory()->create();
        $tec2 = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'status' => 'in_progress',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // OT de verificación: asignada por sup1 a tec1, completada
        WorkOrder::factory()->completed()->create([
            'client_id' => $cliente->id,
            'ticket_id' => $ticket->id,
            'service_type' => 'verificacion_instalacion',
            'code' => 'OT-VER-0001',
            'technician_id' => $tec1->id,
            'assigned_by' => $sup1->id,
            'created_at' => Carbon::now()->subDays(2),
            'assigned_at' => Carbon::now()->subDays(2)->addHours(1),
            'started_at' => Carbon::now()->subDays(2)->addHours(2),
            'completed_date' => Carbon::now()->subDays(2)->addHours(3),
        ]);

        // OT de instalación: asignada por sup2 a tec2, en progreso
        WorkOrder::factory()->inProgress()->create([
            'client_id' => $cliente->id,
            'ticket_id' => $ticket->id,
            'service_type' => 'instalacion',
            'code' => 'OT-INS-0002',
            'technician_id' => $tec2->id,
            'assigned_by' => $sup2->id,
            'created_at' => Carbon::now()->subDays(1),
            'assigned_at' => Carbon::now()->subDays(1)->addHours(1),
            'started_at' => Carbon::now()->subDays(1)->addHours(2),
        ]);

        $service = new TimelineService();
        $timeline = $service->buildFromTicket($ticket);

        $areaKeys = array_column($timeline['areas'], 'key');
        $this->assertContains('supervisor_0', $areaKeys);
        $this->assertContains('supervisor_1', $areaKeys);
        $this->assertContains('technician_0', $areaKeys);
        $this->assertContains('technician_1', $areaKeys);

        $labels = array_column($timeline['areas'], 'label');
        $this->assertCount(4, array_filter($labels, fn ($l) => str_contains($l, 'Fase')));

        // El ticket sigue activo porque hay una OT en progreso
        $this->assertTrue($timeline['parent']['isActive']);
        $this->assertCount(2, $timeline['workOrders']);
    }
}
