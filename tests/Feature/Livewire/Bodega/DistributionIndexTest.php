<?php

namespace Tests\Feature\Livewire\Bodega;

use App\Livewire\Bodega\DistributionIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_shipment_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DistributionIndex::class)
            ->assertSet('search', '');
    }
}
