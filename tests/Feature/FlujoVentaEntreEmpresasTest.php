<?php

namespace Tests\Feature;

use App\Livewire\Bodega\IntercompanySaleCreate;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Company;
use App\Models\CompanyProductInventory;
use App\Models\IntercompanySale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FlujoVentaEntreEmpresasTest extends TestCase
{
    use RefreshDatabase;

    public function test_venta_entre_empresas_descueanta_vendedora_y_suma_compradora()
    {
        $this->actingAs(User::factory()->create());

        $sellerCompany = Company::factory()->create();
        $buyerCompany = Company::factory()->create();
        $seller = Branch::factory()->create(['company_id' => $sellerCompany->id, 'name' => 'Amayo']);
        $buyer = Branch::factory()->create(['company_id' => $buyerCompany->id, 'name' => 'Aguilares']);

        $product = Product::factory()->create([
            'current_stock' => 100,
            'average_cost' => 19.99,
            'total_value' => 1999,
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $seller->id,
            'product_id' => $product->id,
            'allocated_quantity' => 100,
        ]);

        CompanyProductInventory::create([
            'company_id' => $sellerCompany->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'average_cost' => 19.99,
            'total_value' => 1999,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $seller->id)
            ->set('buyerBranchId', $buyer->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 40)
            ->call('save')
            ->assertRedirect(route('bodega.intercompany-sales.index'));

        // Vendedora baja de 100 a 60
        $sellerInv = BranchInventory::where('branch_id', $seller->id)->where('product_id', $product->id)->first();
        $this->assertEquals(60, (float) $sellerInv->allocated_quantity);

        // Compradora sube a 40
        $buyerInv = BranchInventory::where('branch_id', $buyer->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($buyerInv);
        $this->assertEquals(40, (float) $buyerInv->allocated_quantity);

        // Se registró la venta con IVA
        $sale = IntercompanySale::first();
        $this->assertNotNull($sale);
        $this->assertEquals($seller->id, $sale->seller_branch_id);
        $this->assertEquals($buyer->id, $sale->buyer_branch_id);
        $this->assertEquals(40, (int) $sale->items()->first()->quantity);

        $subtotal = 40 * 19.99; // 799.60
        $iva = round($subtotal * 0.13, 2);
        $this->assertEquals(round($subtotal, 2), (float) $sale->subtotal);
        $this->assertEquals($iva, (float) $sale->iva_amount);
        $this->assertEquals(round($subtotal + $iva, 2), (float) $sale->total);

        // Movimientos
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'exit',
            'branch_id' => $seller->id,
            'quantity' => 40,
            'reference_type' => 'intercompany_sale',
            'reference_id' => $sale->id,
        ]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => 'entry',
            'branch_id' => $buyer->id,
            'quantity' => 40,
            'reference_type' => 'intercompany_sale',
            'reference_id' => $sale->id,
        ]);
    }

    public function test_venta_entre_sucursales_de_la_misma_empresa_se_rechaza()
    {
        $this->actingAs(User::factory()->create());

        $company = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $company->id]);
        $branchB = Branch::factory()->create(['company_id' => $company->id]);

        $product = Product::factory()->create(['current_stock' => 50]);

        BranchInventory::factory()->create([
            'branch_id' => $branchA->id,
            'product_id' => $product->id,
            'allocated_quantity' => 50,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $branchA->id)
            ->set('buyerBranchId', $branchB->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 10)
            ->call('save')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertNull(IntercompanySale::first());
    }

    public function test_venta_rechaza_stock_insuficiente_en_vendedora()
    {
        $this->actingAs(User::factory()->create());

        $sellerCompany = Company::factory()->create();
        $buyerCompany = Company::factory()->create();
        $seller = Branch::factory()->create(['company_id' => $sellerCompany->id]);
        $buyer = Branch::factory()->create(['company_id' => $buyerCompany->id]);

        $product = Product::factory()->create(['current_stock' => 10]);

        BranchInventory::factory()->create([
            'branch_id' => $seller->id,
            'product_id' => $product->id,
            'allocated_quantity' => 5,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $seller->id)
            ->set('buyerBranchId', $buyer->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 40)
            ->call('save')
            ->assertDispatched('show-toast', type: 'error');

        $this->assertNull(IntercompanySale::first());
    }

    public function test_venta_actualiza_inventario_a_nivel_de_empresas()
    {
        $this->actingAs(User::factory()->create());

        $sellerCompany = Company::factory()->create();
        $buyerCompany = Company::factory()->create();
        $seller = Branch::factory()->create(['company_id' => $sellerCompany->id]);
        $buyer = Branch::factory()->create(['company_id' => $buyerCompany->id]);

        $product = Product::factory()->create([
            'current_stock' => 100,
            'average_cost' => 20,
            'total_value' => 2000,
        ]);

        BranchInventory::factory()->create([
            'branch_id' => $seller->id,
            'product_id' => $product->id,
            'allocated_quantity' => 100,
        ]);

        CompanyProductInventory::create([
            'company_id' => $sellerCompany->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'average_cost' => 20,
            'total_value' => 2000,
        ]);

        // La empresa compradora ya tenía 50 a $10
        CompanyProductInventory::create([
            'company_id' => $buyerCompany->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'average_cost' => 10,
            'total_value' => 500,
        ]);

        Livewire::test(IntercompanySaleCreate::class)
            ->set('sellerBranchId', $seller->id)
            ->set('buyerBranchId', $buyer->id)
            ->call('selectProduct', $product->id)
            ->set('quantity', 50)
            ->call('save')
            ->assertRedirect(route('bodega.intercompany-sales.index'));

        // Vendedora: 100 - 50 = 50
        $sellerCompanyInv = CompanyProductInventory::where('company_id', $sellerCompany->id)->where('product_id', $product->id)->first();
        $this->assertEquals(50, (float) $sellerCompanyInv->quantity);

        // Compradora: 50 a $10 + 50 a $20 = promedio $15
        $buyerCompanyInv = CompanyProductInventory::where('company_id', $buyerCompany->id)->where('product_id', $product->id)->first();
        $this->assertEquals(100, (float) $buyerCompanyInv->quantity);
        $this->assertEquals(15.0, (float) $buyerCompanyInv->average_cost);
    }
}
