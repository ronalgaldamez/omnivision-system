<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\BranchSwitcher;
use App\Models\Branch;
use App\Models\Company;
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

    public function test_groups_branches_by_company()
    {
        $this->actingAs(User::factory()->create());

        $sociedad = Company::factory()->create(['razon_social' => 'Omnivision S.A. de C.V.']);
        $persona = Company::factory()->create(['razon_social' => 'Jorge Alfredo Argueta Flores']);

        Branch::factory()->create(['company_id' => $sociedad->id, 'name' => 'Sucursal Amayo']);
        Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Sucursal Aguilares']);

        $component = Livewire::test(BranchSwitcher::class);

        $companies = $component->get('companies');
        $this->assertCount(2, $companies);

        $byRazon = $companies->keyBy('razon_social');
        $this->assertEquals('Sucursal Amayo', $byRazon['Omnivision S.A. de C.V.']->branches->first()->name);
        $this->assertEquals('Sucursal Aguilares', $byRazon['Jorge Alfredo Argueta Flores']->branches->first()->name);
    }

    public function test_usuario_solo_puede_ver_sucursales_de_su_empresa()
    {
        $sociedad = Company::factory()->create();
        $persona = Company::factory()->create();

        $branchPersona = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Chalatenango']);

        $user = User::factory()->create(['branch_id' => $branchPersona->id]);

        $this->actingAs($user);

        $allowed = $user->allowedBranchIds();
        $this->assertContains($branchPersona->id, $allowed);
    }

    public function test_no_puede_cambiar_a_sucursal_de_otra_empresa()
    {
        $sociedad = Company::factory()->create();
        $persona = Company::factory()->create();

        $miBranch = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Chalatenango']);
        $otraBranch = Branch::factory()->create(['company_id' => $sociedad->id, 'name' => 'Amayo']);

        $user = User::factory()->create(['branch_id' => $miBranch->id]);
        $this->actingAs($user);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $otraBranch->id)
            ->assertDispatched('show-toast', type: 'error')
            ->assertSet('activeBranchId', $miBranch->id);
    }

    public function test_si_puede_cambiar_a_sucursal_hermana_de_su_misma_empresa()
    {
        $persona = Company::factory()->create();

        $miBranch = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Chalatenango']);
        $hermana = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Aguilares']);

        $user = User::factory()->create(['branch_id' => $miBranch->id]);
        $this->actingAs($user);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $hermana->id)
            ->assertSet('activeBranchId', $hermana->id);
    }

    public function test_usuario_sin_sucursal_puede_cambiar_a_cualquiera()
    {
        $sociedad = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $sociedad->id]);

        $user = User::factory()->create(['branch_id' => null]);
        $this->actingAs($user);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $branch->id)
            ->assertSet('activeBranchId', $branch->id);
    }

    public function test_bodeguero_ve_todas_las_sucursales_incluso_otra_empresa()
    {
        $sociedad = Company::factory()->create();
        $persona = Company::factory()->create();

        $miBranch = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Chalatenango']);
        $otraEmpresa = Branch::factory()->create(['company_id' => $sociedad->id, 'name' => 'Amayo']);

        $role = \App\Models\Role::firstOrCreate(['name' => 'warehouse']);
        $user = User::factory()->create(['branch_id' => $miBranch->id]);
        $user->syncRoles([$role->name]);

        $this->actingAs($user);

        $allowed = $user->allowedBranchIds();
        $this->assertContains($miBranch->id, $allowed);
        $this->assertContains($otraEmpresa->id, $allowed);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $otraEmpresa->id)
            ->assertSet('activeBranchId', $otraEmpresa->id);
    }

    public function test_lista_manual_de_sucursales_permite_otra_empresa()
    {
        $sociedad = Company::factory()->create();
        $persona = Company::factory()->create();

        $miBranch = Branch::factory()->create(['company_id' => $persona->id, 'name' => 'Chalatenango']);
        $otraEmpresa = Branch::factory()->create(['company_id' => $sociedad->id, 'name' => 'Amayo']);

        $user = User::factory()->create(['branch_id' => $miBranch->id]);
        $user->branches()->attach([$miBranch->id, $otraEmpresa->id]);

        $this->actingAs($user);

        $allowed = $user->allowedBranchIds();
        $this->assertContains($miBranch->id, $allowed);
        $this->assertContains($otraEmpresa->id, $allowed);

        Livewire::test(BranchSwitcher::class)
            ->call('switchBranch', $otraEmpresa->id)
            ->assertSet('activeBranchId', $otraEmpresa->id);
    }
}
