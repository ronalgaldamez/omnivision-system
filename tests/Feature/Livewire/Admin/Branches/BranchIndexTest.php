<?php

namespace Tests\Feature\Livewire\Admin\Branches;

use App\Livewire\Admin\Branches\BranchIndex;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_branch_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchIndex::class)
            ->assertSee('Sucursales');
    }

    public function test_toggle_active()
    {
        $this->actingAs(User::factory()->create());

        $branch = Branch::factory()->create(['is_active' => true]);

        Livewire::test(BranchIndex::class)
            ->call('toggleActive', $branch->id);

        $this->assertFalse($branch->fresh()->is_active);
    }

    public function test_delete_branch()
    {
        $this->actingAs(User::factory()->create());

        $branch = Branch::factory()->create();

        Livewire::test(BranchIndex::class)
            ->call('delete', $branch->id);

        $this->assertNull(Branch::find($branch->id));
    }
}
