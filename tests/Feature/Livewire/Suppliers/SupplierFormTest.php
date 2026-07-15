<?php

namespace Tests\Feature\Livewire\Suppliers;

use App\Livewire\Suppliers\SupplierForm;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->assertSet('supplierId', null);
    }

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create();

        Livewire::test(SupplierForm::class, ['id' => $supplier->id])
            ->assertSet('supplierId', $supplier->id)
            ->assertSet('name', $supplier->name);
    }

    public function test_requires_name_and_contact()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->set('name', '')
            ->set('contact_name', '')
            ->call('confirmSave')
            ->assertHasErrors(['name', 'contact_name']);
    }

    public function test_requires_valid_nrc_and_nit()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->set('name', 'Proveedor Test')
            ->set('contact_name', 'Contacto')
            ->set('nrc', 'abc')
            ->set('nit', 'def')
            ->call('confirmSave')
            ->assertHasErrors(['nrc', 'nit']);
    }

    public function test_creates_supplier()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->set('name', 'Proveedor Test')
            ->set('contact_name', 'Juan Pérez')
            ->set('nrc', '12345678')
            ->set('nit', '123456789')
            ->call('confirmSave')
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['name' => 'Proveedor Test']);
    }

    public function test_adds_and_removes_phone()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->assertCount('phones', 0)
            ->call('addPhone')
            ->assertCount('phones', 1)
            ->call('removePhone', 0)
            ->assertCount('phones', 0);
    }

    public function test_adds_and_removes_bank_account()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SupplierForm::class)
            ->assertCount('bankAccounts', 0)
            ->call('addBankAccount')
            ->assertCount('bankAccounts', 1)
            ->call('removeBankAccount', 0)
            ->assertCount('bankAccounts', 0);
    }

    public function test_updates_supplier()
    {
        $this->actingAs(User::factory()->create());

        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        Livewire::test(SupplierForm::class, ['id' => $supplier->id])
            ->set('name', 'Updated Name')
            ->call('save')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['name' => 'Updated Name']);
    }
}
