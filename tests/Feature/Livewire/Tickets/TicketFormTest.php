<?php

namespace Tests\Feature\Livewire\Tickets;

use App\Livewire\Tickets\TicketForm;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_search()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'create tickets']));
        $this->actingAs($user);

        Client::factory()->create(['name' => 'Juan Pérez']);

        Livewire::test(TicketForm::class)
            ->set('clientSearch', 'Juan')
            ->assertCount('clientSearchResults', 1);
    }

    public function test_selects_service_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'create tickets']));
        $this->actingAs($user);

        $serviceType = ServiceType::factory()->create(['name' => 'internet']);

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->assertSet('service_type_id', $serviceType->id);
    }
}
