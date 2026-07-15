<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoRequisicionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tecnico_solicita_bodega_aprueba_y_stock_se_descuenta()
    {
        $tecnico = User::factory()->create();
        $bodeguero = User::factory()->create();
        $sucursal = Branch::factory()->create();

        $producto = Product::factory()->create([
            'current_stock' => 50,
            'average_cost' => 20.00,
            'total_value' => 1000,
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $sucursal->id,
            'product_id' => $producto->id,
            'allocated_quantity' => 10,
        ]);

        $requisicion = Requisition::factory()->create([
            'technician_id' => $tecnico->id,
            'branch_id' => $sucursal->id,
            'status' => 'pending',
        ]);

        RequisitionItem::factory()->create([
            'requisition_id' => $requisicion->id,
            'product_id' => $producto->id,
            'quantity_requested' => 5,
        ]);

        $requisicion->update([
            'status' => 'approved',
            'approved_by' => $bodeguero->id,
            'approved_at' => now(),
        ]);

        $movimiento = Movement::factory()->create([
            'product_id' => $producto->id,
            'user_id' => $bodeguero->id,
            'type' => 'requisition_out',
            'quantity' => 5,
            'branch_id' => $sucursal->id,
        ]);

        $service = new InventoryService();
        $service->processExit($producto, 5, $movimiento);

        $producto->refresh();

        $this->assertEquals(45, $producto->current_stock);
        $this->assertEquals(20.00, (float) $producto->average_cost);
    }

    public function test_requisicion_rechazada_no_afecta_stock()
    {
        $tecnico = User::factory()->create();
        $bodeguero = User::factory()->create();
        $producto = Product::factory()->create(['current_stock' => 10]);

        $requisicion = Requisition::factory()->create([
            'technician_id' => $tecnico->id,
            'status' => 'pending',
        ]);

        RequisitionItem::factory()->create([
            'requisition_id' => $requisicion->id,
            'product_id' => $producto->id,
            'quantity_requested' => 5,
        ]);

        $requisicion->update([
            'status' => 'rejected',
            'rejection_reason' => 'Sin stock disponible',
        ]);

        $producto->refresh();
        $this->assertEquals(10, $producto->current_stock);
    }
}
