<?php

namespace Tests\Feature\Livewire\Bodega;

use App\Livewire\Bodega\DistributionCreate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DistributionCreate::class)
            ->assertSet('branch_id', '');
    }
}
