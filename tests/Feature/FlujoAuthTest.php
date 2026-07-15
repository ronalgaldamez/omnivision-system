<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlujoAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_carga()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_usuario_puede_iniciar_sesion()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_login_falla_con_credenciales_incorrectas()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_no_autenticado_redirigido_a_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
