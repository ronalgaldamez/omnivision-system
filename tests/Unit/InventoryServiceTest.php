<?php

namespace Tests\Unit;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
        $this->user = User::factory()->create();
    }

    protected function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'current_stock' => 0,
            'average_cost' => 0,
            'total_value' => 0,
            'is_obsolete' => false,
            'is_floating' => false,
        ], $overrides));
    }

    protected function makeMovement(array $overrides = []): Movement
    {
        return Movement::create(array_merge([
            'product_id' => 1,
            'user_id' => $this->user->id,
            'type' => 'entry',
            'quantity' => 0,
            'unit_cost' => 0,
            'total_value' => 0,
        ], $overrides));
    }

    // ========== PURCHASE ENTRY TESTS ==========

    public function test_purchase_entry_first_purchase_sets_stock_and_costs()
    {
        $product = $this->makeProduct();
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 10,
        ]);

        $this->service->processPurchaseEntry($product, 10, 5.00, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(10, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(50.0, (float) $product->total_value);
        $this->assertEquals(5.0, (float) $movement->unit_cost);
        $this->assertEquals(50.0, (float) $movement->total_value);
    }

    public function test_purchase_entry_weighted_average_two_purchases()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 5,
        ]);

        $this->service->processPurchaseEntry($product, 5, 8.00, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(15, $product->current_stock);
        $this->assertEquals(6.0, (float) $product->average_cost);
        $this->assertEquals(15 * 6.0, (float) $product->total_value);
        $this->assertEquals(8.0, (float) $movement->unit_cost);
        $this->assertEquals(40.0, (float) $movement->total_value);
    }

    public function test_purchase_entry_obsolete_product_does_not_affect_stock()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
            'is_obsolete' => true,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 5,
        ]);

        $this->service->processPurchaseEntry($product, 5, 8.00, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(10, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(50.0, (float) $product->total_value);
        $this->assertEquals(0.0, (float) $movement->unit_cost);
        $this->assertEquals(0.0, (float) $movement->total_value);
    }

    public function test_purchase_entry_floating_product_does_not_affect_stock()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
            'is_floating' => true,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 5,
        ]);

        $this->service->processPurchaseEntry($product, 5, 8.00, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(10, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(50.0, (float) $product->total_value);
        $this->assertEquals(0.0, (float) $movement->unit_cost);
        $this->assertEquals(0.0, (float) $movement->total_value);
    }

    // ========== EXIT TESTS ==========

    public function test_exit_reduces_stock_and_preserves_average_cost()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 3,
        ]);

        $this->service->processExit($product, 3, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(7, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(35.0, (float) $product->total_value);
        $this->assertEquals(5.0, (float) $movement->unit_cost);
        $this->assertEquals(15.0, (float) $movement->total_value);
    }

    public function test_exit_to_zero_resets_average_cost()
    {
        $product = $this->makeProduct([
            'current_stock' => 5,
            'average_cost' => 5,
            'total_value' => 25,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 5,
        ]);

        $this->service->processExit($product, 5, $movement);

        $product->refresh();

        $this->assertEquals(0, $product->current_stock);
        $this->assertEquals(0.0, (float) $product->average_cost);
        $this->assertEquals(0.0, (float) $product->total_value);
    }

    public function test_exit_obsolete_product_does_not_affect_stock()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
            'is_obsolete' => true,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 3,
        ]);

        $this->service->processExit($product, 3, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(10, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(50.0, (float) $product->total_value);
        $this->assertEquals(0.0, (float) $movement->unit_cost);
        $this->assertEquals(0.0, (float) $movement->total_value);
    }

    public function test_exit_floating_product_does_not_affect_stock()
    {
        $product = $this->makeProduct([
            'current_stock' => 10,
            'average_cost' => 5,
            'total_value' => 50,
            'is_floating' => true,
        ]);
        $movement = $this->makeMovement([
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 3,
        ]);

        $this->service->processExit($product, 3, $movement);

        $product->refresh();
        $movement->refresh();

        $this->assertEquals(10, $product->current_stock);
        $this->assertEquals(5.0, (float) $product->average_cost);
        $this->assertEquals(50.0, (float) $product->total_value);
        $this->assertEquals(0.0, (float) $movement->unit_cost);
        $this->assertEquals(0.0, (float) $movement->total_value);
    }
}
