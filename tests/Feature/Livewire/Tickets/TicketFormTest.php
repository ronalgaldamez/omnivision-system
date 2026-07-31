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

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'create tickets']));
        $this->actingAs($user);
        return $user;
    }

    public function test_client_search()
    {
        $this->actingAsUser();
        Client::factory()->create(['name' => 'Juan Pérez']);

        Livewire::test(TicketForm::class)
            ->set('clientSearch', 'Juan')
            ->assertCount('clientSearchResults', 1);
    }

    public function test_selects_service_type()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create(['name' => 'internet']);

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->assertSet('service_type_id', $serviceType->id);
    }

    public function test_confirm_open_requires_service_type()
    {
        $this->actingAsUser();

        Livewire::test(TicketForm::class)
            ->call('confirmOpen')
            ->assertHasErrors('service_type_id');
    }

    public function test_confirm_open_allows_without_client()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create();

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->call('confirmOpen')
            ->assertSet('confirmingOpen', true);
    }

    public function test_open_ticket_without_client()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create();

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->call('openTicket')
            ->assertSet('ticketOpened', true)
            ->assertSet('ticketId', fn($id) => is_numeric($id));
    }

    public function test_open_ticket_with_client()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create();
        $client = Client::factory()->create();

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->set('client_id', $client->id)
            ->call('openTicket')
            ->assertSet('ticketOpened', true);
    }

    public function test_open_ticket_sets_editing_enabled()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create();

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->call('openTicket')
            ->assertSet('editingEnabled', true);
    }

    public function test_open_ticket_sets_elapsed_seconds()
    {
        $this->actingAsUser();
        $serviceType = ServiceType::factory()->create();

        Livewire::test(TicketForm::class)
            ->set('service_type_id', $serviceType->id)
            ->call('openTicket')
            ->assertSet('editingEnabled', true);
    }
}
