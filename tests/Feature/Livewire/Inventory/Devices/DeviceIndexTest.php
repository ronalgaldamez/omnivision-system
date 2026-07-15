<?php

namespace Tests\Feature\Livewire\Inventory\Devices;

use App\Livewire\Inventory\Devices\DeviceIndex;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeviceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_device_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DeviceIndex::class)
            ->assertSee('Dispositivos');
    }

    public function test_shows_devices()
    {
        $this->actingAs(User::factory()->create());

        Device::factory()->count(3)->create();

        Livewire::test(DeviceIndex::class)
            ->assertSee('Dispositivos');
    }

    public function test_search_by_mac()
    {
        $this->actingAs(User::factory()->create());

        Device::factory()->create(['mac_address' => '00:1A:2B:3C:4D:5E']);
        Device::factory()->create(['mac_address' => '00:1A:2B:3C:4D:5F']);

        Livewire::test(DeviceIndex::class)
            ->set('search', '5E')
            ->assertSee('5E');
    }

    public function test_filter_by_status()
    {
        $this->actingAs(User::factory()->create());

        Device::factory()->inStock()->create();
        Device::factory()->create(['status' => 'damaged']);

        Livewire::test(DeviceIndex::class)
            ->set('statusFilter', 'in_stock')
            ->assertSet('statusFilter', 'in_stock');
    }
}
