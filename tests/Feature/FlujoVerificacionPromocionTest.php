<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\VerificationPricingService;
use App\Services\VerificationPromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoVerificacionPromocionTest extends TestCase
{
    use RefreshDatabase;

    private function makeVerificationWorkOrder(array $ticketData = [], array $woData = []): array
    {
        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();
        ServiceType::factory()->create([
            'name' => 'verificacion_instalacion',
            'requires_ot' => true,
            'requires_contract' => false,
        ]);

        $ticket = Ticket::factory()->create(array_merge([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'service_type' => 'verificacion_instalacion',
            'status' => 'in_progress',
            'requires_contract' => false,
        ], $ticketData));

        $workOrder = WorkOrder::factory()->create(array_merge([
            'client_id' => $cliente->id,
            'ticket_id' => $ticket->id,
            'technician_id' => $usuario->id,
            'created_by' => $usuario->id,
            'service_type' => 'verificacion_instalacion',
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(30),
        ], $woData));

        return [$ticket, $workOrder, $usuario];
    }

    public function test_aprobar_verificacion_promueve_ticket_y_genera_contrato()
    {
        [$ticket, $workOrder, $usuario] = $this->makeVerificationWorkOrder();

        $service = new VerificationPromotionService(new VerificationPricingService());

        $this->actingAs($usuario);
        $contract = $service->approve($workOrder, 250.00);

        $ticket->refresh();
        $workOrder->refresh();

        $this->assertSame('promoted', $ticket->promotion_status);
        $this->assertNotNull($ticket->promoted_at);
        $this->assertSame('250.00', $ticket->contract_price_snapshot);
        $this->assertTrue((bool) $ticket->requires_contract);
        $this->assertSame('in_progress', $ticket->status);

        $this->assertSame('completed', $workOrder->status);
        $this->assertNotNull($workOrder->completed_date);

        $this->assertNotNull($contract);
        $this->assertSame($ticket->id, $contract->ticket_id);
        $this->assertSame('instalacion', $contract->service_type);
        $this->assertEquals(250.0, (float) $contract->installation_cost);
        $this->assertSame('pending', $contract->status);
    }

    public function test_rechazar_verificacion_cierra_ticket_con_motivo()
    {
        [$ticket, $workOrder] = $this->makeVerificationWorkOrder();

        $service = new VerificationPromotionService(new VerificationPricingService());

        $service->reject($workOrder, 'Excede metros gratis y el cliente no acepta el cobro.');

        $ticket->refresh();
        $workOrder->refresh();

        $this->assertSame('rejected', $ticket->promotion_status);
        $this->assertSame('Excede metros gratis y el cliente no acepta el cobro.', $ticket->rejection_reason);
        $this->assertSame('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);

        $this->assertSame('completed', $workOrder->status);
        $this->assertFalse((bool) $workOrder->customer_accepts_cost);
    }

    public function test_aprobar_con_excedente_sin_aceptacion_lanza_error()
    {
        [$ticket, $workOrder, $usuario] = $this->makeVerificationWorkOrder(
            [],
            ['drop_distance' => 200, 'customer_accepts_cost' => false]
        );

        $service = new VerificationPromotionService(new VerificationPricingService());

        $this->actingAs($usuario);
        $this->expectException(\RuntimeException::class);

        $service->approve($workOrder, 250.00);
    }

    public function test_aprobar_con_excedente_y_aceptacion_promueve()
    {
        [$ticket, $workOrder, $usuario] = $this->makeVerificationWorkOrder(
            [],
            ['drop_distance' => 200, 'customer_accepts_cost' => true]
        );

        $service = new VerificationPromotionService(new VerificationPricingService());

        $this->actingAs($usuario);
        $contract = $service->approve($workOrder, 250.00);

        $ticket->refresh();
        $workOrder->refresh();

        $this->assertSame('promoted', $ticket->promotion_status);
        $this->assertSame('completed', $workOrder->status);
        $this->assertTrue((bool) $workOrder->customer_accepts_cost);
        $this->assertNotNull($contract);
    }

    public function test_precio_sugerido_respeta_reglas_de_distancia()
    {
        ServiceType::factory()->create([
            'name' => 'verificacion_instalacion',
            'requires_ot' => true,
            'requires_contract' => false,
        ]);

        \App\Models\ServiceRule::create([
            'service_type_id' => ServiceType::where('name', 'verificacion_instalacion')->first()->id,
            'rule_key' => 'free_distance',
            'rule_value' => ['meters' => 100],
            'is_active' => true,
        ]);
        \App\Models\ServiceRule::create([
            'service_type_id' => ServiceType::where('name', 'verificacion_instalacion')->first()->id,
            'rule_key' => 'price_per_meter',
            'rule_value' => ['amount' => 8],
            'is_active' => true,
        ]);

        $cliente = Client::factory()->create();
        $usuario = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'service_type' => 'verificacion_instalacion',
            'status' => 'in_progress',
        ]);

        $service = new VerificationPricingService();
        $rules = $service->rulesFor($ticket);

        // 100m gratis, 8$/m extra: 150m -> 50*8 = 400
        $this->assertSame(100, $rules['free_distance']);
        $this->assertSame(8.0, $rules['price_per_meter']);
        $this->assertSame(400.0, $service->suggestedPrice(150, $rules));
        // Dentro del rango gratis: 0
        $this->assertSame(0.0, $service->suggestedPrice(80, $rules));
    }

    public function test_aprobacion_no_duplica_contrato()
    {
        [$ticket, $workOrder, $usuario] = $this->makeVerificationWorkOrder();

        $service = new VerificationPromotionService(new VerificationPricingService());

        $this->actingAs($usuario);
        $contract = $service->approve($workOrder, 100.00);
        $contractAgain = $service->approve($workOrder, 100.00);

        $this->assertSame($contract->id, $contractAgain->id);
        $this->assertSame(1, Contract::where('ticket_id', $ticket->id)->count());
    }

    public function test_aprobacion_precarga_tv_extra_en_contrato()
    {
        // OT de verificación con 2 TVs extra anotadas por el técnico
        [$ticket, $workOrder, $usuario] = $this->makeVerificationWorkOrder(
            [],
            ['extra_tvs' => 2]
        );

        $service = new VerificationPromotionService(new VerificationPricingService());

        $this->actingAs($usuario);
        $contract = $service->approve($workOrder, 100.00);

        $contract->refresh();

        $this->assertSame(2, $contract->extra_tvs);
        // TV extra: instalación $6 POR TV (2*6=12), mensual FIJO +$1 (2 o 3 TVs = +$1).
        $this->assertEquals(12.0, (float) $contract->tv_install_fee);  // 2 * 6
        $this->assertEquals(1.0, (float) $contract->monthly_extra_fee); // FIJO +$1
        // Se crearon los cargos de TV extra
        $this->assertSame(2, $contract->charges()->count());
    }
}
