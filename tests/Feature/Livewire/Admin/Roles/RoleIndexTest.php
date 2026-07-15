<?php

namespace Tests\Feature\Livewire\Admin\Roles;

use App\Livewire\Admin\Roles\RoleIndex;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_role_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(RoleIndex::class)
            ->assertSee('Roles');
    }

    public function test_delete_role()
    {
        $this->actingAs(User::factory()->create());

        $role = Role::create(['name' => 'test_role', 'guard_name' => 'web']);

        Livewire::test(RoleIndex::class)
            ->call('delete', $role->id);

        $this->assertNull(Role::find($role->id));
    }
}
