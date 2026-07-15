<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoOTTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_completa_ot_y_usa_materiales()
    {
        $tecnico = User::factory()->create();
        $cliente = Client::factory()->create();
        $producto = Product::factory()->create([
            'current_stock' => 20,
            'average_cost' => 15.00,
            'total_value' => 300,
        ]);

        $ot = WorkOrder::factory()->create([
            'technician_id' => $tecnico->id,
            'client_id' => $cliente->id,
            'status' => 'in_progress',
            'started_at' => now()->subHours(2),
        ]);

        WorkOrderMaterial::factory()->create([
            'work_order_id' => $ot->id,
            'product_id' => $producto->id,
            'quantity_used' => 3,
        ]);

        $movimiento = Movement::factory()->create([
            'product_id' => $producto->id,
            'user_id' => $tecnico->id,
            'type' => 'technician_out',
            'quantity' => 3,
        ]);

        $service = new InventoryService();
        $service->processExit($producto, 3, $movimiento);

        $ot->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $producto->refresh();

        $this->assertEquals(17, $producto->current_stock);
        $this->assertEquals('completed', $ot->fresh()->status);
        $this->assertNotNull($ot->fresh()->completed_date);
    }


}
