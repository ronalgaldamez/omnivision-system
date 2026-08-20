<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Plan;
use App\Models\Zone;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoPromocionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_meses_gratis_cable_12_al_14()
    {
        $plan = Plan::factory()->create(['service_type' => 'cable', 'speed' => '50 Mbps']);
        $zona = Zone::factory()->create();

        Campaign::create([
            'name' => 'Meses gratis cable',
            'type' => 'discount_months',
            'category' => 'contract_rule',
            'zone_id' => $zona->id,
            'config' => ['min_pay' => 12, 'free' => 2],
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $service = new PromotionService();
        $result = $service->freeMonths($plan->id, $zona->id, 12);

        $this->assertTrue($result['applies']);
        $this->assertEquals(2, $result['free']);
        $this->assertEquals(12, $result['pay']);
    }

    public function test_meses_gratis_cable_24_al_28()
    {
        $plan = Plan::factory()->create(['service_type' => 'cable']);
        $zona = Zone::factory()->create();

        Campaign::create([
            'name' => 'Meses gratis cable 24',
            'type' => 'discount_months',
            'category' => 'contract_rule',
            'zone_id' => $zona->id,
            'config' => ['min_pay' => 24, 'free' => 4],
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $service = new PromotionService();
        $result = $service->freeMonths($plan->id, $zona->id, 24);

        $this->assertTrue($result['applies']);
        $this->assertEquals(4, $result['free']);
    }

    public function test_doble_velocidad_si_aplica()
    {
        $plan = Plan::factory()->create(['service_type' => 'internet', 'speed' => '100 Mbps']);
        $zona = Zone::factory()->create();

        Campaign::create([
            'name' => 'Doble velocidad',
            'type' => 'double_speed',
            'category' => 'contract_rule',
            'zone_id' => $zona->id,
            'config' => ['enabled' => true],
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $service = new PromotionService();
        $speed = $service->effectiveSpeed($plan->id, $zona->id, 14);

        $this->assertTrue($speed['doubled']);
        $this->assertEquals(200, $speed['effective']);
        $this->assertEquals(100, $speed['base']);
    }

    public function test_doble_velocidad_no_aplica_sin_campana()
    {
        $plan = Plan::factory()->create(['service_type' => 'internet', 'speed' => '100 Mbps']);
        $zona = Zone::factory()->create();

        $service = new PromotionService();
        $speed = $service->effectiveSpeed($plan->id, $zona->id, 14);

        $this->assertFalse($speed['doubled']);
        $this->assertEquals(100, $speed['effective']);
    }

    public function test_meses_gratis_elige_campana_segun_plazo_pagado()
    {
        $plan = Plan::factory()->create(['service_type' => 'cable']);
        $zona = Zone::factory()->create();

        // Dos campañas: una de 12 (regala 2), otra de 24 (regala 4)
        Campaign::create(['name' => 'Cable 12', 'type' => 'discount_months', 'category' => 'contract_rule', 'service' => 'cable', 'zone_id' => $zona->id, 'config' => ['min_pay' => 12, 'free' => 2], 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(), 'is_active' => true]);
        Campaign::create(['name' => 'Cable 24', 'type' => 'discount_months', 'category' => 'contract_rule', 'service' => 'cable', 'zone_id' => $zona->id, 'config' => ['min_pay' => 24, 'free' => 4], 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(), 'is_active' => true]);

        $service = new PromotionService();

        // Paga 12 -> regala 2 (no 4)
        $r12 = $service->freeMonths($plan->id, $zona->id, 12);
        $this->assertTrue($r12['applies']);
        $this->assertEquals(2, $r12['free']);
        $this->assertEquals(12, $r12['pay']);

        // Paga 24 -> regala 4
        $r24 = $service->freeMonths($plan->id, $zona->id, 24);
        $this->assertTrue($r24['applies']);
        $this->assertEquals(4, $r24['free']);
        $this->assertEquals(24, $r24['pay']);
    }

    public function test_meses_gratis_no_aplica_a_internet_ni_combo()
    {
        $zona = Zone::factory()->create();
        Campaign::create(['name' => 'Meses', 'type' => 'discount_months', 'category' => 'contract_rule', 'service' => 'cable', 'zone_id' => $zona->id, 'config' => ['min_pay' => 12, 'free' => 2], 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(), 'is_active' => true]);

        $service = new PromotionService();

        // Internet -> NO meses gratis (la campaña es solo de Cable)
        $internet = Plan::factory()->create(['service_type' => 'internet']);
        $this->assertFalse($service->freeMonths($internet->id, $zona->id, 12)['applies']);

        // Combo -> NO meses gratis
        $combo = Plan::factory()->create(['service_type' => 'internet_cable']);
        $this->assertFalse($service->freeMonths($combo->id, $zona->id, 12)['applies']);
    }

    public function test_doble_velocidad_no_aplica_a_cable_solo()
    {
        $zona = Zone::factory()->create();
        Campaign::create(['name' => 'Doble', 'type' => 'double_speed', 'category' => 'contract_rule', 'service' => 'internet_cable', 'zone_id' => $zona->id, 'config' => ['enabled' => true], 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(), 'is_active' => true]);

        $service = new PromotionService();

        // Cable solo -> NO doble velocidad (la campaña es solo de Combo)
        $cable = Plan::factory()->create(['service_type' => 'cable']);
        $this->assertFalse($service->appliesDoubleSpeed($cable->id, $zona->id, 12));

        // Combo -> SÍ doble velocidad
        $combo = Plan::factory()->create(['service_type' => 'internet_cable']);
        $this->assertTrue($service->appliesDoubleSpeed($combo->id, $zona->id, 12));
    }
}
