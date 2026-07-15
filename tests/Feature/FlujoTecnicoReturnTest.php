<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\Product;
use App\Models\TechnicianReturn;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoTecnicoReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_devuelve_producto_danado_y_stock_se_restaura()
    {
        $tecnico = User::factory()->create();
        $producto = Product::factory()->create([
            'current_stock' => 50,
            'average_cost' => 25.00,
            'total_value' => 1250,
        ]);

        $devolucion = TechnicianReturn::factory()->create([
            'user_id' => $tecnico->id,
            'product_id' => $producto->id,
            'quantity' => 3,
            'type' => 'damage',
        ]);

        $movimiento = Movement::factory()->create([
            'product_id' => $producto->id,
            'user_id' => $tecnico->id,
            'type' => 'technician_return',
            'quantity' => 3,
        ]);

        $service = new InventoryService();
        $service->processPurchaseEntry($producto, 3, $producto->average_cost, $movimiento);

        $producto->refresh();

        $this->assertEquals(53, $producto->current_stock);
        $this->assertEquals(25.00, (float) $producto->average_cost);
    }

    public function test_tecnico_devuelve_sin_stock_previo()
    {
        $tecnico = User::factory()->create();
        $producto = Product::factory()->create([
            'current_stock' => 0,
            'average_cost' => 0,
            'total_value' => 0,
        ]);

        TechnicianReturn::factory()->create([
            'user_id' => $tecnico->id,
            'product_id' => $producto->id,
            'quantity' => 5,
            'type' => 'return',
        ]);

        $movimiento = Movement::factory()->create([
            'product_id' => $producto->id,
            'user_id' => $tecnico->id,
            'type' => 'technician_return',
            'quantity' => 5,
        ]);

        $service = new InventoryService();
        $service->processPurchaseEntry($producto, 5, 0, $movimiento);

        $producto->refresh();

        $this->assertEquals(5, $producto->current_stock);
    }
}
