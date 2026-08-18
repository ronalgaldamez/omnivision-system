<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contract;
use App\Services\ContractDeliveryService;
use App\Services\ContractPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoEntregaContratoTest extends TestCase
{
    use RefreshDatabase;

    private function makeContract(array $clientData = [], array $contractData = []): array
    {
        $client = Client::factory()->create(array_merge([
            'email' => 'cliente@test.com',
            'phone' => '7000-0000',
            'contact_channels' => ['email'],
        ], $clientData));

        $contract = Contract::create(array_merge([
            'client_id' => $client->id,
            'service_type' => 'instalacion',
            'status' => 'ready_to_send',
            'created_by' => null,
        ], $contractData));

        return [$client, $contract];
    }

    public function test_envio_por_correo_marca_contrato_activo()
    {
        [$client, $contract] = $this->makeContract();

        $service = new ContractDeliveryService(app(ContractPdfService::class));
        $result = $service->send($contract);

        $contract->refresh();

        $this->assertTrue($result['email']);
        $this->assertFalse($result['whatsapp']);
        $this->assertSame('active', $contract->status);
        $this->assertNotNull($contract->sent_at);
    }

    public function test_envio_por_whatsapp_genera_enlace()
    {
        [$client, $contract] = $this->makeContract([
            'email' => null,
            'phone' => '7000-0000',
            'contact_channels' => ['whatsapp'],
        ]);

        $service = new ContractDeliveryService(app(ContractPdfService::class));
        $result = $service->send($contract);

        $contract->refresh();

        $this->assertFalse($result['email']);
        $this->assertTrue($result['whatsapp']);
        $this->assertSame('active', $contract->status);

        $shareUrl = $service->whatsAppShareUrl($contract);
        $this->assertNotNull($shareUrl);
        $this->assertStringContainsString('wa.me/', $shareUrl);
    }

    public function test_envio_por_ambos_canales()
    {
        [$client, $contract] = $this->makeContract([
            'contact_channels' => ['email', 'whatsapp'],
        ]);

        $service = new ContractDeliveryService(app(ContractPdfService::class));
        $result = $service->send($contract);

        $contract->refresh();

        $this->assertTrue($result['email']);
        $this->assertTrue($result['whatsapp']);
        $this->assertSame('active', $contract->status);
    }

    public function test_sin_canales_activa_sin_enviar()
    {
        [$client, $contract] = $this->makeContract([
            'email' => null,
            'phone' => '7000-0000',
            'contact_channels' => [],
        ]);

        $service = new ContractDeliveryService(app(ContractPdfService::class));
        $result = $service->send($contract);

        $contract->refresh();

        $this->assertFalse($result['email']);
        $this->assertFalse($result['whatsapp']);
        $this->assertSame('active', $contract->status);
    }
}
