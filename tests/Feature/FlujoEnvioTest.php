<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DistributionShipment;
use App\Models\DistributionShipmentItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoEnvioTest extends TestCase
{
    use RefreshDatabase;

    public function test_envio_pasa_por_todos_los_estados()
    {
        $creador = User::factory()->create();
        $sucursal = Branch::factory()->create();

        $envio = DistributionShipment::factory()->create([
            'branch_id' => $sucursal->id,
            'created_by' => $creador->id,
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $envio->status);
        $this->assertStringStartsWith('ENV-', $envio->code);

        $envio->update([
            'status' => 'in_transit',
            'in_transit_at' => now(),
        ]);

        $this->assertEquals('in_transit', $envio->fresh()->status);
        $this->assertNotNull($envio->fresh()->in_transit_at);

        $envio->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->assertEquals('delivered', $envio->fresh()->status);
        $this->assertNotNull($envio->fresh()->delivered_at);

        $confirmador = User::factory()->create();
        $envio->update([
            'status' => 'confirmed',
            'confirmed_by' => $confirmador->id,
            'confirmed_at' => now(),
        ]);

        $this->assertEquals('confirmed', $envio->fresh()->status);
        $this->assertNotNull($envio->fresh()->confirmed_by);
    }

    public function test_envio_con_items_y_codigo_auto_generado()
    {
        $creador = User::factory()->create();
        $sucursal = Branch::factory()->create();

        $envio = DistributionShipment::factory()->create([
            'branch_id' => $sucursal->id,
            'created_by' => $creador->id,
        ]);

        $producto = Product::factory()->create();

        $item = DistributionShipmentItem::factory()->create([
            'shipment_id' => $envio->id,
            'product_id' => $producto->id,
            'product_name' => $producto->name,
            'quantity' => 15,
        ]);

        $this->assertEquals($envio->id, $item->shipment_id);
        $this->assertEquals(15, (float) $item->quantity);
    }
}
