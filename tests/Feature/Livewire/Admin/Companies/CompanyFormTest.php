<?php

namespace Tests\Feature\Livewire\Admin\Companies;

use App\Livewire\Admin\Companies\CompanyForm;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CompanyForm::class)
            ->assertSee('Razón social');
    }

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();

        Livewire::test(CompanyForm::class, ['id' => $company->id])
            ->assertSet('razonSocial', $company->razon_social);
    }

    public function test_creates_company()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CompanyForm::class)
            ->set('razonSocial', 'Omnivision S.A. de C.V.')
            ->set('nombreComercial', 'Omnivision')
            ->set('tipo', 'sociedad')
            ->call('save')
            ->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseHas('companies', ['razon_social' => 'Omnivision S.A. de C.V.']);
    }

    public function test_requires_razon_social()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CompanyForm::class)
            ->set('razonSocial', '')
            ->call('save')
            ->assertHasErrors('razonSocial');
    }

    public function test_updates_company()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create(['razon_social' => 'Empresa Vieja']);

        Livewire::test(CompanyForm::class, ['id' => $company->id])
            ->set('razonSocial', 'Empresa Nueva')
            ->call('save')
            ->assertRedirect(route('admin.companies.index'));

        $this->assertDatabaseHas('companies', ['razon_social' => 'Empresa Nueva']);
    }
}
