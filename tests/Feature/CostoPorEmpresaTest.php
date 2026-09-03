<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyProductInventory;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostoPorEmpresaTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'current_stock' => 0,
            'average_cost' => 0,
            'total_value' => 0,
        ], $overrides));
    }

    public function test_compra_crea_registro_de_costo_por_empresa()
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $movement = Movement::factory()->entry()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 100,
            'branch_id' => $branch->id,
        ]);

        app(InventoryService::class)->processCompanyPurchaseEntry(
            $company->id,
            $product,
            100,
            19.99,
            $movement
        );

        $this->assertDatabaseHas('company_product_inventories', [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        $record = CompanyProductInventory::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(19.99, (float) $record->average_cost);
        $this->assertEquals(1999.0, (float) $record->total_value);
    }

    public function test_dos_empresas_tienen_costos_independientes()
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $product = $this->makeProduct();
        $user = User::factory()->create();

        // Empresa A compra 100 a $10
        $movA = Movement::factory()->entry()->create(['product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 100]);
        app(InventoryService::class)->processCompanyPurchaseEntry($companyA->id, $product, 100, 10, $movA);

        // Empresa B compra 100 a $50
        $movB = Movement::factory()->entry()->create(['product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 100]);
        app(InventoryService::class)->processCompanyPurchaseEntry($companyB->id, $product, 100, 50, $movB);

        $recordA = CompanyProductInventory::where('company_id', $companyA->id)->where('product_id', $product->id)->first();
        $recordB = CompanyProductInventory::where('company_id', $companyB->id)->where('product_id', $product->id)->first();

        $this->assertEquals(10.0, (float) $recordA->average_cost);
        $this->assertEquals(50.0, (float) $recordB->average_cost);
        $this->assertNotEquals($recordA->average_cost, $recordB->average_cost);
    }

    public function test_promedio_ponderado_acumula_compras_de_la_misma_empresa()
    {
        $company = Company::factory()->create();
        $product = $this->makeProduct();
        $user = User::factory()->create();

        // Compra 1: 100 a $10
        $mov1 = Movement::factory()->entry()->create(['product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 100]);
        app(InventoryService::class)->processCompanyPurchaseEntry($company->id, $product, 100, 10, $mov1);

        // Compra 2: 100 a $20 → promedio ponderado = (1000 + 2000)/200 = 15
        $mov2 = Movement::factory()->entry()->create(['product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 100]);
        app(InventoryService::class)->processCompanyPurchaseEntry($company->id, $product, 100, 20, $mov2);

        $record = CompanyProductInventory::where('company_id', $company->id)->where('product_id', $product->id)->first();

        $this->assertEquals(200, (float) $record->quantity);
        $this->assertEquals(15.0, (float) $record->average_cost);
        $this->assertEquals(3000.0, (float) $record->total_value);
    }

    public function test_helper_retorna_costo_por_empresa()
    {
        $companyA = Company::factory()->create();
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $movA = Movement::factory()->entry()->create(['product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 50]);
        app(InventoryService::class)->processCompanyPurchaseEntry($companyA->id, $product, 50, 7.5, $movA);

        $this->assertEquals(7.5, CompanyProductInventory::averageCostFor($companyA->id, $product->id));
        $this->assertNull(CompanyProductInventory::averageCostFor(999999, $product->id));
    }
}
