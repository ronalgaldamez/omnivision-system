<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Company;
use App\Models\Device;
use App\Models\DistributionShipment;
use App\Models\DistributionShipmentItem;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoTraspasoTest extends TestCase
{
    use RefreshDatabase;

    private function makeDeliveredShipment(Branch $origin, Branch $dest, Product $product, float $qty): DistributionShipment
    {
        $shipment = DistributionShipment::factory()->delivered()->create([
            'origin_branch_id' => $origin->id,
            'branch_id' => $dest->id,
        ]);

        DistributionShipmentItem::factory()->create([
            'shipment_id' => $shipment->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $qty,
        ]);

        return $shipment;
    }

    public function test_traspaso_descuenta_origen_y_suma_destino()
    {
        $company = Company::factory()->create();
        $origin = Branch::factory()->create(['company_id' => $company->id]);
        $dest = Branch::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'current_stock' => 100,
            'average_cost' => 19.99,
            'total_value' => 1999,
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $origin->id,
            'product_id' => $product->id,
            'allocated_quantity' => 100,
        ]);

        $shipment = $this->makeDeliveredShipment($origin, $dest, $product, 40);

        $user = User::factory()->create();
        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Bodega\DistributionReceive::class)
            ->set('code', $shipment->code)
            ->call('search')
            ->call('confirm');

        $originInv = BranchInventory::where('branch_id', $origin->id)->where('product_id', $product->id)->first();
        $destInv = BranchInventory::where('branch_id', $dest->id)->where('product_id', $product->id)->first();

        $this->assertEquals(60, (float) $originInv->allocated_quantity);
        $this->assertEquals(40, (float) $destInv->allocated_quantity);

        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'exit',
            'branch_id' => $origin->id,
            'quantity' => 40,
            'reference_type' => 'shipment',
            'reference_id' => $shipment->id,
        ]);

        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'branch_id' => $dest->id,
            'quantity' => 40,
            'reference_type' => 'shipment',
            'reference_id' => $shipment->id,
        ]);

        $this->assertEquals('confirmed', $shipment->fresh()->status);
    }

    public function test_traspaso_mueve_dispositivos_de_origen_a_destino()
    {
        $company = Company::factory()->create();
        $origin = Branch::factory()->create(['company_id' => $company->id]);
        $dest = Branch::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create([
            'current_stock' => 10,
            'average_cost' => 50,
            'total_value' => 500,
        ]);

        // Producto requiere registro de dispositivo
        $category = \App\Models\Category::factory()->create(['requires_device_registration' => true]);
        $product->update(['category_id' => $category->id]);

        for ($i = 0; $i < 5; $i++) {
            Device::factory()->create([
                'product_id' => $product->id,
                'branch_id' => $origin->id,
                'status' => 'in_stock',
            ]);
        }

        $shipment = $this->makeDeliveredShipment($origin, $dest, $product, 3);

        $user = User::factory()->create();
        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Bodega\DistributionReceive::class)
            ->set('code', $shipment->code)
            ->call('search')
            ->call('confirm');

        $this->assertEquals(2, Device::where('product_id', $product->id)->where('branch_id', $origin->id)->count());
        $this->assertEquals(3, Device::where('product_id', $product->id)->where('branch_id', $dest->id)->count());
    }

    public function test_traspaso_rechaza_stock_insuficiente_en_origen()
    {
        $company = Company::factory()->create();
        $origin = Branch::factory()->create(['company_id' => $company->id]);
        $dest = Branch::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['current_stock' => 10]);

        BranchInventory::factory()->create([
            'branch_id' => $origin->id,
            'product_id' => $product->id,
            'allocated_quantity' => 5,
        ]);

        $shipment = $this->makeDeliveredShipment($origin, $dest, $product, 40);

        $user = User::factory()->create();
        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Bodega\DistributionReceive::class)
            ->set('code', $shipment->code)
            ->call('search')
            ->call('confirm')
            ->assertDispatched('show-toast', type: 'error');

        $originInv = BranchInventory::where('branch_id', $origin->id)->where('product_id', $product->id)->first();
        $this->assertEquals(5, (float) $originInv->allocated_quantity);
        $this->assertNotEquals('confirmed', $shipment->fresh()->status);
    }
}
