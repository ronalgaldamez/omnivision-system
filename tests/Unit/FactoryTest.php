<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_factory(): void
    {
        $branch = Branch::factory()->create();
        $this->assertNotNull($branch->name);
        $this->assertNotNull($branch->code);
    }

    public function test_brand_factory(): void
    {
        $brand = Brand::factory()->create();
        $this->assertNotNull($brand->name);
    }

    public function test_category_factory(): void
    {
        $category = Category::factory()->create();
        $this->assertNotNull($category->name);
    }

    public function test_product_factory(): void
    {
        $product = Product::factory()->create();
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->sku);
        $this->assertNotNull($product->brand_id);
    }

    public function test_client_factory(): void
    {
        $client = Client::factory()->create();
        $this->assertNotNull($client->name);
        $this->assertNotNull($client->branch_id);
    }

    public function test_purchase_factory(): void
    {
        $purchase = Purchase::factory()->create();
        $this->assertNotNull($purchase->invoice_number);
        $this->assertGreaterThan(0, $purchase->total);
    }

    public function test_ticket_factory(): void
    {
        $ticket = Ticket::factory()->create();
        $this->assertNotNull($ticket->ticket_code);
        $this->assertNotNull($ticket->client_id);
    }

    public function test_work_order_factory(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $this->assertNotNull($workOrder->code);
        $this->assertNotNull($workOrder->technician_id);
    }

    public function test_user_factory_with_branch(): void
    {
        $user = User::factory()->withBranch()->create();
        $this->assertNotNull($user->branch_id);
    }

    public function test_ticket_states(): void
    {
        $open = Ticket::factory()->open()->create();
        $this->assertEquals('open', $open->status);

        $resolved = Ticket::factory()->resolved()->create();
        $this->assertEquals('resolved', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
    }

    public function test_work_order_states(): void
    {
        $completed = WorkOrder::factory()->completed()->create();
        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_date);
    }

    public function test_product_states(): void
    {
        $obsolete = Product::factory()->obsolete()->create();
        $this->assertTrue($obsolete->is_obsolete);

        $floating = Product::factory()->floating()->create();
        $this->assertTrue($floating->is_floating);
    }
}
