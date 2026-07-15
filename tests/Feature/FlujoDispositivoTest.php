<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Product;
use App\Models\User;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoDispositivoTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispositivo_se_registra_asigna_e_instala()
    {
        $producto = Product::factory()->create();
        $compra = Purchase::factory()->create();
        $tecnico = User::factory()->create();
        $sucursal = Branch::factory()->create();

        $dispositivo = Device::factory()->inStock()->create([
            'product_id' => $producto->id,
            'purchase_id' => $compra->id,
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'status' => 'in_stock',
        ]);

        $this->assertEquals('in_stock', $dispositivo->status);
        $this->assertNotNull($dispositivo->mac_address);

        $dispositivo->update([
            'status' => 'assigned',
            'technician_id' => $tecnico->id,
            'assigned_at' => now(),
            'branch_id' => $sucursal->id,
        ]);

        $this->assertEquals('assigned', $dispositivo->fresh()->status);
        $this->assertEquals($tecnico->id, $dispositivo->fresh()->technician_id);

        $ot = WorkOrder::factory()->create([
            'technician_id' => $tecnico->id,
        ]);

        $dispositivo->update([
            'status' => 'installed',
            'work_order_id' => $ot->id,
            'installed_at' => now(),
        ]);

        $this->assertEquals('installed', $dispositivo->fresh()->status);
        $this->assertEquals($ot->id, $dispositivo->fresh()->work_order_id);
        $this->assertNotNull($dispositivo->fresh()->installed_at);
    }

    public function test_ciclo_de_vida_completo_dispositivo()
    {
        $producto = Product::factory()->create();
        $compra = Purchase::factory()->create();

        $d = Device::factory()->create([
            'product_id' => $producto->id,
            'purchase_id' => $compra->id,
            'mac_address' => '00:1A:2B:3C:4D:5F',
            'status' => 'in_stock',
        ]);

        $this->assertEquals('in_stock', $d->status);

        $d->update(['status' => 'assigned']);
        $this->assertEquals('assigned', $d->fresh()->status);

        $d->update(['status' => 'installed']);
        $this->assertEquals('installed', $d->fresh()->status);

        $d->update(['status' => 'damaged']);
        $this->assertEquals('damaged', $d->fresh()->status);
    }
}
