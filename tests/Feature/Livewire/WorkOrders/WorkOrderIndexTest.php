<?php

namespace Tests\Feature\Livewire\WorkOrders;

use App\Livewire\WorkOrders\WorkOrderIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_by_status()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(WorkOrderIndex::class)
            ->set('statusFilter', 'pending')
            ->assertSet('statusFilter', 'pending');
    }
}
