<?php

namespace Tests\Feature\Livewire\Inventory\Devices;

use App\Livewire\Inventory\Devices\DeviceShow;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeviceShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_device_detail()
    {
        $this->actingAs(User::factory()->create());

        $device = Device::factory()->create();

        Livewire::test(DeviceShow::class, ['id' => $device->id])
            ->assertSee($device->mac_address);
    }

    public function test_shows_device_status()
    {
        $this->actingAs(User::factory()->create());

        $device = Device::factory()->inStock()->create();

        Livewire::test(DeviceShow::class, ['id' => $device->id])
            ->assertSet('device.id', $device->id);
    }

    public function test_404_for_invalid_device()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(DeviceShow::class, ['id' => 999]);
    }
}
