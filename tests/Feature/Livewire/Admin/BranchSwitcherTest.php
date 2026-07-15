<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\BranchSwitcher;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_switcher()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchSwitcher::class)
            ->assertSet('activeBranchId', '');
    }

    public function test_shows_active_branches()
    {
        $this->actingAs(User::factory()->create());

        Branch::factory()->create(['is_active' => true, 'name' => 'Sucursal A']);

        Livewire::test(BranchSwitcher::class)
            ->assertSee('Sucursal A');
    }

    public function test_switch_branch()
    {
        $this->actingAs(User::factory()->create());

        $branch = Branch::factory()->create(['is_active' => true]);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $branch->id)
            ->assertSet('activeBranchId', $branch->id);

        $this->assertEquals($branch->id, session('active_branch_id'));
    }

    public function test_clear_branch_filter()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', '')
            ->assertSet('activeBranchId', '');

        $this->assertNull(session('active_branch_id'));
    }
}
