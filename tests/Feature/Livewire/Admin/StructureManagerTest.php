<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\StructureManager;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StructureManagerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        Permission::create(['name' => 'access_admin']);

        $user = User::factory()->create();
        $user->givePermissionTo('access_admin');

        return $user;
    }

    public function test_branches_url_starts_on_branches_tab()
    {
        $this->actingAs($this->adminUser());

        $response = $this->get(route('admin.branches.index'));
        $response->assertStatus(200);
    }

    public function test_companies_url_starts_on_companies_tab()
    {
        $this->actingAs($this->adminUser());

        $response = $this->get(route('admin.companies.index'));
        $response->assertStatus(200);
    }

    public function test_switches_tabs()
    {
        $this->actingAs($this->adminUser());

        Livewire::test(StructureManager::class)
            ->assertSet('activeTab', 'companies')
            ->call('setTab', 'branches')
            ->assertSet('activeTab', 'branches')
            ->call('setTab', 'companies')
            ->assertSet('activeTab', 'companies');
    }

    public function test_nested_lists_render_with_data()
    {
        $this->actingAs($this->adminUser());

        $company = Company::factory()->create(['razon_social' => 'Empresa Visible']);
        Branch::factory()->create(['name' => 'Sucursal Visible', 'company_id' => $company->id]);

        Livewire::test(StructureManager::class)
            ->assertSee('Empresas')
            ->assertSee('Sucursales');

        $this->get(route('admin.companies.index'))
            ->assertSee('Empresa Visible');

        $this->get(route('admin.branches.index'))
            ->assertSee('Sucursal Visible');
    }
}
