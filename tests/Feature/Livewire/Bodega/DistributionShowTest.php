<?php

namespace Tests\Feature\Livewire\Bodega;

use App\Livewire\Bodega\DistributionShow;
use App\Models\DistributionShipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_for_invalid_shipment()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(DistributionShow::class, ['id' => 999]);
    }
}
