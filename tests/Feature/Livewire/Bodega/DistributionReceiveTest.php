<?php

namespace Tests\Feature\Livewire\Bodega;

use App\Livewire\Bodega\DistributionReceive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionReceiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_receive_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DistributionReceive::class)
            ->assertSet('code', '');
    }
}
