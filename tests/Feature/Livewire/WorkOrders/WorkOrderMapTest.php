<?php

namespace Tests\Feature\Livewire\WorkOrders;

use App\Livewire\WorkOrders\WorkOrderMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_map()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(WorkOrderMap::class);
    }
}
