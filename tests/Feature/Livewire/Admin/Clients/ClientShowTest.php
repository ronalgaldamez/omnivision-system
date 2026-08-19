<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\Clients\ClientShow;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Plan;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_client_contracts()
    {
        $client = Client::factory()->create();
        $plan = Plan::factory()->create();

        Contract::create([
            'client_id' => $client->id,
            'service_type' => 'instalacion',
            'plan_id' => $plan->id,
            'status' => 'active',
            'contract_date' => now(),
            'created_by' => null,
        ]);

        Livewire::test(ClientShow::class, ['id' => $client->id])
            ->assertOk()
            ->assertSet('contracts', function ($contracts) {
                return $contracts->count() === 1
                    && $contracts->first()->client_id === $contracts->first()->client_id;
            });
    }

    public function test_renders_empty_state_when_no_contracts()
    {
        $client = Client::factory()->create();

        Livewire::test(ClientShow::class, ['id' => $client->id])
            ->assertOk()
            ->assertSet('contracts', fn ($contracts) => $contracts->isEmpty());
    }
}
