<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Campaign;
use App\Models\InstallFeeRule;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Zone;
use App\Models\ZonePlanPrice;
use App\Services\VerificationPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoTarifasInstalacionTest extends TestCase
{
    use RefreshDatabase;

    private function makeTicketWithZone($serviceContracted = 'internet', ?Zone $zone = null): Ticket
    {
        $cliente = Client::factory()->create([
            'zone_id' => $zone?->id,
        ]);
        $usuario = User::factory()->create();
        ServiceType::factory()->create(['name' => 'verificacion_instalacion']);

        $ticket = Ticket::factory()->create([
            'client_id' => $cliente->id,
            'created_by' => $usuario->id,
            'service_type' => 'verificacion_instalacion',
            'zone_id' => $zone?->id,
            'status' => 'in_progress',
        ]);

        // Crear contrato para definir el tipo de servicio contratado
        Contract::create([
            'client_id' => $cliente->id,
            'ticket_id' => $ticket->id,
            'service_type' => 'instalacion',
            'service_contracted' => $serviceContracted,
            'zone_id' => $zone?->id,
            'status' => 'pending',
            'created_by' => $usuario->id,
        ]);

        return $ticket;
    }

    public function test_tarifa_por_zona_según_servicio()
    {
        $zona = Zone::factory()->create(['name' => 'Tejutla']);

        InstallFeeRule::create([
            'zone_id' => $zona->id,
            'service_type' => 'internet',
            'covered_meters' => 150,
            'fee' => 25,
            'excess_per_50m' => 5,
        ]);

        $ticket = $this->makeTicketWithZone('internet', $zona);
        $service = new VerificationPricingService();

        $fee = $service->installFeeFor($ticket);

        $this->assertSame('internet', 'internet');
        $this->assertEquals(150, $fee['covered_meters']);
        $this->assertEquals(25.0, $fee['fee']);
        $this->assertEquals(5.0, $fee['excess_per_50m']);
    }

    public function test_costo_instalacion_dentro_de_metros_cubre_base()
    {
        $zona = Zone::factory()->create();

        InstallFeeRule::create([
            'zone_id' => $zona->id,
            'service_type' => 'internet',
            'covered_meters' => 150,
            'fee' => 25,
            'excess_per_50m' => 5,
        ]);

        $ticket = $this->makeTicketWithZone('internet', $zona);
        $service = new VerificationPricingService();

        $fee = $service->installFeeFor($ticket);
        // 100m dentro de 150m -> solo cargo base $25
        $this->assertEquals(25.0, $service->suggestedInstallCost(100, $fee));
        // 150m justo -> cargo base $25
        $this->assertEquals(25.0, $service->suggestedInstallCost(150, $fee));
    }

    public function test_costo_instalacion_con_exceso()
    {
        $zona = Zone::factory()->create();

        InstallFeeRule::create([
            'zone_id' => $zona->id,
            'service_type' => 'cable',
            'covered_meters' => 150,
            'fee' => 15,
            'excess_per_50m' => 5,
        ]);

        $ticket = $this->makeTicketWithZone('cable', $zona);
        $service = new VerificationPricingService();

        $fee = $service->installFeeFor($ticket);

        // 200m: excede 50m -> 1 bloque -> $15 + $5 = $20
        $this->assertEquals(20.0, $service->suggestedInstallCost(200, $fee));
        // 250m: excede 100m -> 2 bloques -> $15 + $10 = $25
        $this->assertEquals(25.0, $service->suggestedInstallCost(250, $fee));
        // 170m: excede 20m -> 1 bloque (fracción) -> $15 + $5 = $20
        $this->assertEquals(20.0, $service->suggestedInstallCost(170, $fee));
    }

    public function test_campana_instalacion_gratis_dentro_de_metros()
    {
        $zona = Zone::factory()->create();

        Campaign::create([
            'name' => 'Mes del Padre',
            'type' => 'free_installation',
            'zone_id' => $zona->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        InstallFeeRule::create([
            'zone_id' => $zona->id,
            'service_type' => 'internet',
            'covered_meters' => 150,
            'fee' => 25,
            'excess_per_50m' => 5,
        ]);

        $ticket = $this->makeTicketWithZone('internet', $zona);
        $service = new VerificationPricingService();

        // Dentro de 150m con campaña activa -> gratis
        $this->assertEquals(0.0, $service->suggestedInstallCostFor($ticket, 120));
    }

    public function test_campana_instalacion_gratis_solo_dentro_de_metros()
    {
        $zona = Zone::factory()->create();

        Campaign::create([
            'name' => 'Mes del Padre',
            'type' => 'free_installation',
            'zone_id' => $zona->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        InstallFeeRule::create([
            'zone_id' => $zona->id,
            'service_type' => 'internet',
            'covered_meters' => 150,
            'fee' => 25,
            'excess_per_50m' => 5,
        ]);

        $ticket = $this->makeTicketWithZone('internet', $zona);
        $service = new VerificationPricingService();

        // Campaña de instalación gratis activa: la base se perdona.
        // Excede 150m (200m), solo se cobra el recargo por los 50m extra -> $5.
        $this->assertEquals(5.0, $service->suggestedInstallCostFor($ticket, 200));
    }
}
