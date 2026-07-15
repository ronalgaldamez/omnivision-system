<?php

namespace Tests\Feature\Livewire\WorkOrders;

use App\Livewire\WorkOrders\WorkOrderShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_for_invalid_order()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(WorkOrderShow::class, ['id' => 999]);
    }
}
