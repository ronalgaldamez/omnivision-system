<?php

namespace Tests\Feature\Livewire\Bodega;

use App\Livewire\Bodega\RequisitionBodegaIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RequisitionBodegaIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_requisition_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(RequisitionBodegaIndex::class)
            ->assertSet('filterStatus', '');
    }
}
