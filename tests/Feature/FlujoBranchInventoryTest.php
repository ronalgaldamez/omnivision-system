<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\DistributionShipment;
use App\Models\DistributionShipmentItem;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoBranchInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_envio_confirmado_incrementa_inventario_sucursal()
    {
        $sucursal = Branch::factory()->create();
        $creador = User::factory()->create();
        $producto = Product::factory()->create([
            'current_stock' => 100,
            'average_cost' => 10.00,
            'total_value' => 1000,
        ]);

        $envio = DistributionShipment::factory()->delivered()->create([
            'branch_id' => $sucursal->id,
            'created_by' => $creador->id,
        ]);

        DistributionShipmentItem::factory()->create([
            'shipment_id' => $envio->id,
            'product_id' => $producto->id,
            'product_name' => $producto->name,
            'quantity' => 20,
        ]);

        $confirmer = User::factory()->create();
        $envio->update([
            'status' => 'confirmed',
            'confirmed_by' => $confirmer->id,
            'confirmed_at' => now(),
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $sucursal->id,
            'product_id' => $producto->id,
            'allocated_quantity' => 20,
        ]);

        $inventario = BranchInventory::where('branch_id', $sucursal->id)
            ->where('product_id', $producto->id)
            ->first();

        $this->assertNotNull($inventario);
        $this->assertEquals(20, (float) $inventario->allocated_quantity);
    }

    public function test_inventario_sucursal_se_actualiza_al_recibir()
    {
        $sucursal = Branch::factory()->create();
        $producto = Product::factory()->create();

        $inventario = BranchInventory::factory()->create([
            'branch_id' => $sucursal->id,
            'product_id' => $producto->id,
            'allocated_quantity' => 50,
        ]);

        $inventario->increment('allocated_quantity', 10);

        $this->assertEquals(60, (float) $inventario->fresh()->allocated_quantity);

        $inventario->decrement('allocated_quantity', 5);

        $this->assertEquals(55, (float) $inventario->fresh()->allocated_quantity);
    }
}
