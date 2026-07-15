<?php

namespace Tests\Feature\Livewire\Admin\Branches;

use App\Livewire\Admin\Branches\BranchForm;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchForm::class)
            ->assertSee('Sucursal');
    }

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $branch = Branch::factory()->create();

        Livewire::test(BranchForm::class, ['id' => $branch->id])
            ->assertSet('name', $branch->name);
    }

    public function test_creates_branch()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchForm::class)
            ->set('name', 'Sucursal Norte')
            ->set('code', 'SN-01')
            ->call('save')
            ->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', ['code' => 'SN-01']);
    }

    public function test_requires_name_and_code()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(BranchForm::class)
            ->set('name', '')
            ->set('code', '')
            ->call('save')
            ->assertHasErrors(['name', 'code']);
    }

    public function test_updates_branch()
    {
        $this->actingAs(User::factory()->create());

        $branch = Branch::factory()->create(['name' => 'Old Name']);

        Livewire::test(BranchForm::class, ['id' => $branch->id])
            ->set('name', 'New Name')
            ->call('save')
            ->assertRedirect(route('admin.branches.index'));

        $this->assertDatabaseHas('branches', ['name' => 'New Name']);
    }
}
