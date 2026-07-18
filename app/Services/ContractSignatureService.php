<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractSignature;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

class ContractSignatureService
{
    /**
     * Guarda una firma digital (base64) para un contrato.
     */
    public function saveSignature(
        Contract $contract,
        string $type,
        string $signatureData,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ContractSignature {
        $signature = $contract->signatures()->updateOrCreate(
            ['type' => $type],
            [
                'signature_data' => $signatureData,
                'ip_address' => $ipAddress ?? Request::ip(),
                'user_agent' => $userAgent ?? Request::userAgent(),
                'signed_at' => now(),
            ]
        );

        // Si ya están todas las firmas, actualizar el contrato
        if ($contract->isFullySigned()) {
            $contract->update(['signed_at' => now()]);
        }

        return $signature;
    }

    /**
     * Genera un enlace único para que el cliente firme remotamente.
     */
    public function generateSignatureLink(Contract $contract): string
    {
        $token = Str::random(64);

        $contract->signatures()->updateOrCreate(
            ['type' => 'client'],
            ['signature_token' => $token]
        );

        return route('public.contract.sign', ['token' => $token]);
    }

    /**
     * Verifica un token de firma y devuelve el contrato asociado.
     */
    public function verifySignatureLink(string $token): ?Contract
    {
        $signature = ContractSignature::where('signature_token', $token)
            ->whereNull('signed_at')
            ->first();

        return $signature?->contract;
    }

    /**
     * Verifica si un contrato tiene todas las firmas requeridas.
     */
    public function isFullySigned(Contract $contract): bool
    {
        return $contract->isFullySigned();
    }

    /**
     * Obtiene la firma del cliente como imagen base64 para el PDF.
     */
    public function getClientSignatureForPdf(Contract $contract): ?string
    {
        $signature = $contract->signatures()
            ->where('type', 'client')
            ->first();

        return $signature?->signature_data;
    }

    /**
     * Obtiene la firma del agente de ventas para el PDF.
     */
    public function getSalesRepSignatureForPdf(Contract $contract): ?string
    {
        $signature = $contract->signatures()
            ->where('type', 'sales_rep')
            ->first();

        return $signature?->signature_data;
    }
}
