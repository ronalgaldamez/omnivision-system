<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoPagoAbonoTest extends TestCase
{
    use RefreshDatabase;

    public function test_abono_proporcional_con_tv_extra()
    {
        $cliente = Client::factory()->create();

        $contract = Contract::create([
            'client_id' => $cliente->id,
            'service_type' => 'instalacion',
            'price' => 66.00,
            'monthly_extra_fee' => 1.00, // 1 TV extra
            'extra_tvs' => 1,
            'payment_day' => 15,
            'status' => 'ready_to_send',
            'created_by' => null,
        ]);

        // Simular instalación 10 días antes del día de pago (15)
        $instalacion = \Carbon\Carbon::createFromFormat('Y-m-d', '2026-08-05');
        $result = $contract->abonoProporcional($instalacion);

        $this->assertNotNull($result);
        // Cuota base = 66 + 1 = 67
        $this->assertEquals(67.0, $result['base']);
        // 10 días de servicio
        $this->assertEquals(10, $result['days']);
        // 67 ÷ 31 × 10 = 21.61 aprox
        $this->assertEquals(21.61, $result['charge']);
    }

    public function test_abono_base_sin_tv_extra()
    {
        $cliente = Client::factory()->create();

        $contract = Contract::create([
            'client_id' => $cliente->id,
            'service_type' => 'instalacion',
            'price' => 66.00,
            'monthly_extra_fee' => 0.00,
            'payment_day' => 15,
            'status' => 'ready_to_send',
            'created_by' => null,
        ]);

        $instalacion = \Carbon\Carbon::createFromFormat('Y-m-d', '2026-08-05');
        $result = $contract->abonoProporcional($instalacion);

        // 66 ÷ 31 × 10 = 21.29
        $this->assertEquals(21.29, $result['charge']);
        $this->assertEquals(66.0, $result['base']);
    }

    public function test_tv_extra_fees_se_leen_de_regla_por_zona()
    {
        $zona = \App\Models\Zone::factory()->create();

        \App\Models\Campaign::create([
            'name' => 'TV extra zona',
            'type' => 'tv_extra',
            'category' => 'contract_rule',
            'service' => 'all',
            'zone_id' => $zona->id,
            'config' => ['install_fee' => 7, 'monthly_fee' => 2],
            'is_active' => true,
        ]);

        $fees = \App\Services\TvExtraFees::forZone($zona->id);

        $this->assertEquals(7.0, $fees['install_fee']);
        $this->assertEquals(2.0, $fees['monthly_fee']);
    }

    public function test_tv_extra_fees_default_sin_regla()
    {
        $fees = \App\Services\TvExtraFees::forZone(null);

        $this->assertEquals(6.0, $fees['install_fee']);
        $this->assertEquals(1.0, $fees['monthly_fee']);
    }
}
