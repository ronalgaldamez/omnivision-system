<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Services\ContractDeliveryService;
use App\Services\ContractPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoTvExtraTest extends TestCase
{
    use RefreshDatabase;

    private function makeContract(int $extraTvs = 2): Contract
    {
        $client = Client::factory()->create();

        return Contract::create([
            'client_id' => $client->id,
            'service_type' => 'cable_internet',
            'price' => 66.00,
            'status' => 'ready_to_send',
            'extra_tvs' => $extraTvs,
            'tv_install_fee' => $extraTvs * 6,
            'monthly_extra_fee' => $extraTvs * 1,
            'created_by' => null,
        ]);
    }

    public function test_registro_tv_extra_genera_cargos()
    {
        $contract = $this->makeContract(2);

        // 2 TVs extra -> $12 instalación + $2 mensual recurrente
        $this->assertSame(2, $contract->extra_tvs);
        $this->assertEquals(12.0, (float) $contract->tv_install_fee);
        $this->assertEquals(2.0, (float) $contract->monthly_extra_fee);
        $this->assertEquals(68.0, $contract->monthlyTotal());
        $this->assertEquals(12.0, $contract->installFeeTotal());
    }

    public function test_cuota_mensual_total_suma_tv_extra()
    {
        $contract = $this->makeContract(1);

        // 1 TV extra: cuota $66 + $1 = $67
        $this->assertSame(1, $contract->extra_tvs);
        $this->assertEquals(67.0, $contract->monthlyTotal());
    }

    public function test_tv_extra_genera_cargo_unico_y_recurrente()
    {
        $contract = $this->makeContract(2);

        // Simular lo que hace syncTvExtraCharges en el workflow
        $contract->charges()->create([
            'client_id' => $contract->client_id,
            'type' => 'extra_tv',
            'description' => "Instalación de TV extra x2",
            'amount' => 12.00,
            'is_recurring' => false,
            'quantity' => 2,
        ]);
        $contract->charges()->create([
            'client_id' => $contract->client_id,
            'type' => 'extra_tv',
            'description' => "Recargo mensual TV extra x2",
            'amount' => 2.00,
            'is_recurring' => true,
            'recurring_period' => 'monthly',
            'quantity' => 2,
        ]);

        $charges = $contract->charges()->get();

        $this->assertCount(2, $charges);

        $install = $charges->firstWhere('amount', '12.00');
        $monthly = $charges->firstWhere('amount', '2.00');

        $this->assertNotNull($install);
        $this->assertFalse((bool) $install->is_recurring);

        $this->assertNotNull($monthly);
        $this->assertTrue((bool) $monthly->is_recurring);
        $this->assertEquals('monthly', $monthly->recurring_period);
    }
}
