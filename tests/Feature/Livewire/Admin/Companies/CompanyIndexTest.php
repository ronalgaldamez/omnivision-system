<?php

namespace Tests\Feature\Livewire\Admin\Companies;

use App\Livewire\Admin\Companies\CompanyIndex;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_index()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CompanyIndex::class)
            ->assertSee('Empresas');
    }

    public function test_shows_companies()
    {
        $this->actingAs(User::factory()->create());

        Company::factory()->create(['razon_social' => 'Empresa Test']);

        Livewire::test(CompanyIndex::class)
            ->assertSee('Empresa Test');
    }

    public function test_delete_detaches_branches_and_removes_company()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id, 'is_active' => false]);

        Livewire::test(CompanyIndex::class)
            ->call('delete', $company->id);

        $this->assertNull($branch->fresh()->company_id);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_delete_blocked_when_company_has_active_branches()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();
        Branch::factory()->create(['company_id' => $company->id, 'is_active' => true]);

        Livewire::test(CompanyIndex::class)
            ->call('delete', $company->id);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }
}
