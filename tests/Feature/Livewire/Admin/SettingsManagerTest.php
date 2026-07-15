<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\SettingsManager;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_settings()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SettingsManager::class)
            ->assertSet('otRequired', false);
    }

    public function test_toggle_ot_required()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SettingsManager::class)
            ->set('otRequired', true);

        $this->assertEquals('true', Setting::get('ot_required'));
    }

    public function test_loads_service_types()
    {
        $this->actingAs(User::factory()->create());

        ServiceType::factory()->create(['name' => 'internet']);

        Livewire::test(SettingsManager::class)
            ->assertSet('serviceTypes', function ($types) {
                return $types->count() === 1;
            });
    }

    public function test_creates_service_type()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SettingsManager::class)
            ->call('openServiceModal')
            ->assertSet('showServiceModal', true)
            ->set('serviceName', 'Nuevo Servicio')
            ->call('saveService');

        $this->assertDatabaseHas('service_types', ['name' => 'Nuevo Servicio']);
    }

    public function test_deletes_service_type()
    {
        $this->actingAs(User::factory()->create());

        $serviceType = ServiceType::factory()->create(['name' => 'test']);

        Livewire::test(SettingsManager::class)
            ->call('deleteService', $serviceType->id);

        $this->assertNull(ServiceType::find($serviceType->id));
    }
}
