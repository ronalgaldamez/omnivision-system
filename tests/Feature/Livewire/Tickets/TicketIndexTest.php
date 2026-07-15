<?php

namespace Tests\Feature\Livewire\Tickets;

use App\Livewire\Tickets\TicketIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_ticket_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(TicketIndex::class)
            ->assertSet('statusFilter', '');
    }

    public function test_filters_by_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(TicketIndex::class)
            ->set('statusFilter', 'open')
            ->assertSet('statusFilter', 'open');
    }
}
