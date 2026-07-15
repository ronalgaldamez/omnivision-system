<?php

namespace Tests\Feature\Livewire\Admin\Roles;

use App\Livewire\Admin\Roles\RoleForm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(RoleForm::class)
            ->assertSee('Rol');
    }

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        Role::create(['name' => 'test_role', 'guard_name' => 'web']);

        Livewire::test(RoleForm::class, ['id' => 1])
            ->assertSet('name', 'test_role');
    }

    public function test_creates_role()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(RoleForm::class)
            ->set('name', 'new_role')
            ->set('prefix', 'NR')
            ->call('save');

        $this->assertDatabaseHas('roles', ['name' => 'new_role']);
    }

    public function test_changes_tab()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(RoleForm::class)
            ->call('setTab', 'inventario')
            ->assertSet('activeTab', 'inventario');
    }
}
