<?php

namespace Tests\Feature\Livewire\Inventory\Devices;

use App\Livewire\Inventory\Devices\DeviceRegister;
use App\Models\Device;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeviceRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_register_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DeviceRegister::class)
            ->assertSee('Registro de Dispositivos');
    }

    public function test_product_search()
    {
        $this->actingAs(User::factory()->create());

        Product::factory()->create(['name' => 'Router']);

        Livewire::test(DeviceRegister::class)
            ->set('productSearch', 'Rout')
            ->assertCount('productResults', 1);
    }

    public function test_select_product_generates_rows()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->assertSet('product_id', $product->id)
            ->assertCount('rows', 1);
    }

    public function test_add_device_row()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->set('quantity', 3)
            ->assertCount('rows', 3);
    }

    public function test_remove_device_row()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->set('quantity', 3)
            ->call('removeRow', 0)
            ->assertCount('rows', 2);
    }

    public function test_requires_mac_address()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->set('rows.0.mac_address', '')
            ->call('requestSave')
            ->assertHasErrors('rows.0.mac_address');
    }

    public function test_saves_device()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->set('rows.0.mac_address', '00:1A:2B:3C:4D:5E')
            ->call('requestSave')
            ->call('confirmSave');

        $this->assertDatabaseHas('devices', [
            'product_id' => $product->id,
            'mac_address' => '00:1A:2B:3C:4D:5E',
        ]);
    }

    public function test_rejects_duplicate_mac()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();
        Device::factory()->create([
            'product_id' => $product->id,
            'mac_address' => '00:1A:2B:3C:4D:5E',
        ]);

        Livewire::test(DeviceRegister::class)
            ->call('selectProduct', $product->id)
            ->set('rows.0.mac_address', '00:1A:2B:3C:4D:5E')
            ->call('requestSave')
            ->assertDispatched('show-toast');
    }

    public function test_clear_product_resets_rows()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DeviceRegister::class)
            ->call('clearProduct')
            ->assertSet('product_id', '')
            ->assertCount('rows', 0);
    }

    public function test_reset_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DeviceRegister::class)
            ->call('resetForm')
            ->assertSet('product_id', '')
            ->assertSet('quantity', 1);
    }
}
