<?php

namespace Tests\Feature\Livewire\Admin\Users;

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_user_list()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(UserIndex::class)
            ->assertSee('Usuarios');
    }

    public function test_toggle_active()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create(['is_active' => true]);

        Livewire::test(UserIndex::class)
            ->call('toggleActive', $user->id);

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_delete_user()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create();

        Livewire::test(UserIndex::class)
            ->call('delete', $user->id);

        $this->assertNull(User::find($user->id));
    }
}
