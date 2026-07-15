<?php

namespace Tests\Feature\Livewire\Admin\Users;

use App\Livewire\Admin\Users\UserForm;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create();

        Livewire::test(UserForm::class, ['id' => $user->id])
            ->assertSet('name', $user->name);
    }

    public function test_updates_user()
    {
        $this->actingAs(User::factory()->create());

        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::factory()->create();

        Livewire::test(UserForm::class, ['id' => $user->id])
            ->set('name', 'Updated Name')
            ->set('selectedRole', 'admin')
            ->call('save');

        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_changes_tab()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(UserForm::class)
            ->call('setTab', 'permisos')
            ->assertSet('activeTab', 'permisos');
    }
}
