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
     * Envía el contrato por los canales elegidos por el cliente y lo marca como activo.
     * Puede enviar por email Y generar enlace de WhatsApp a la vez.
     */
    public function send(Contract $contract): array
    {
        if (!$this->pdfService->hasPdf($contract)) {
            $this->pdfService->generate($contract);
        }

        $client = $contract->client;
        $channels = $client?->contact_channels ?? [];
        $sentByEmail = false;
        $sentByWhatsApp = false;

        if (in_array('email', $channels) && $client?->email) {
            Mail::to($client->email)->send(new ContractMail($contract));
            $sentByEmail = true;
        }

        if (in_array('whatsapp', $channels)) {
            $sentByWhatsApp = true;
        }

        $contract->update([
            'status' => 'active',
            'signed_at' => $contract->signed_at ?? now(),
            'sent_at' => now(),
        ]);

        return [
            'email' => $sentByEmail,
            'whatsapp' => $sentByWhatsApp,
        ];
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
