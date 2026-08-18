<?php

namespace App\Services;

use App\Mail\ContractMail;
use App\Models\Contract;
use Illuminate\Support\Facades\Mail;

/**
 * Gestiona el envío de contratos al cliente según su preferencia de contacto.
 * - email: envía el PDF adjunto por correo.
 * - whatsapp: genera un enlace de descarga para compartir por WhatsApp.
 * - ninguno: marca el contrato como activo sin enviar.
 */
class ContractDeliveryService
{
    public function __construct(private ContractPdfService $pdfService) {}

    /**
     * Envía el contrato por el canal preferido del cliente y lo marca como activo.
     */
    public function send(Contract $contract): array
    {
        if (!$this->pdfService->hasPdf($contract)) {
            $this->pdfService->generate($contract);
        }

        $client = $contract->client;
        $preference = $client?->contact_preference ?? 'ninguno';
        $result = ['channel' => $preference, 'sent' => false];

        switch ($preference) {
            case 'email':
                if ($client?->email) {
                    Mail::to($client->email)->send(new ContractMail($contract));
                    $result['sent'] = true;
                }
                break;

            case 'whatsapp':
                $result['sent'] = true;
                break;
        }

        $contract->update([
            'status' => 'active',
            'signed_at' => $contract->signed_at ?? now(),
            'sent_at' => now(),
        ]);

        return $result;
    }

    /**
     * Enlace de WhatsApp para compartir el PDF del contrato.
     */
    public function whatsAppShareUrl(Contract $contract): ?string
    {
        $client = $contract->client;
        if (!$client?->phone) {
            return null;
        }

        $url = $this->pdfService->getPdfUrl($contract);
        if (!$url) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $client->phone);
        if (!str_starts_with($phone, '503')) {
            $phone = '503' . $phone;
        }

        $message = "Hola {$client->name}, aquí está tu contrato de Omnivisión ({$contract->contract_digital_code}): {$url}";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
