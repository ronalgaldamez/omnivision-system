<?php

namespace Tests\Feature\Livewire\Admin\Users;

use App\Livewire\Admin\Users\UserCreate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(UserCreate::class)
            ->assertSet('name', '');
    }

    public function test_creates_user()
    {
        $this->actingAs(User::factory()->create());

        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        Livewire::test(UserCreate::class)
            ->set('name', 'Nuevo Usuario')
            ->set('email', 'nuevo@test.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('selectedRole', 'admin')
            ->call('save')
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);
    }

    public function test_requires_fields()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(UserCreate::class)
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password', 'selectedRole']);
    }
}
