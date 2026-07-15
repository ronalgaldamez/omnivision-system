<?php

namespace Tests\Feature\Livewire\WorkOrders;

use App\Livewire\WorkOrders\WorkOrderForm;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $order = WorkOrder::factory()->create();

        Livewire::test(WorkOrderForm::class, ['id' => $order->id])
            ->assertSet('orderId', $order->id);
    }

    public function test_client_search()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'create work_orders']));
        $this->actingAs($user);

        Client::factory()->create(['name' => 'Juan Pérez']);

        Livewire::test(WorkOrderForm::class)
            ->set('clientSearch', 'Juan')
            ->assertCount('clientSearchResults', 1);
    }

    public function test_selects_service_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'create work_orders']));
        $this->actingAs($user);

        $serviceType = ServiceType::factory()->create(['name' => 'internet']);

        Livewire::test(WorkOrderForm::class)
            ->set('service_type_id', $serviceType->id)
            ->assertSet('service_type_id', $serviceType->id);
    }
}
