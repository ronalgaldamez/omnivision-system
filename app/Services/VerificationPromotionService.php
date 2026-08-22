<?php

namespace App\Services;

use App\Events\TicketPromotedToContract;
use App\Models\Contract;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

/**
 * Orquesta la promoción de una verificación de instalación en campo:
 * - approve(): hereda el ticket a la fase de contratación y genera el contrato.
 * - reject(): cierra el ticket como verificación no factible.
 */
class VerificationPromotionService
{
    public function __construct(private VerificationPricingService $pricing) {}

    /**
     * Aprueba la verificación: completa la OT, promueve el ticket y
     * genera el contrato precargado para los agentes de contratos.
     */
    public function approve(WorkOrder $workOrder, float $price): Contract
    {
        $ticket = $workOrder->ticket;
        if (!$ticket) {
            throw new \RuntimeException('La OT de verificación no tiene ticket asociado.');
        }

        // Si la distancia excede la franja gratis, el cliente debe haber aceptado el costo.
        $rules = $this->pricing->rulesFor($ticket);
        $freeDistance = (int) ($rules['free_distance'] ?? 150);
        $hasExcess = (float) ($workOrder->drop_distance ?? 0) > $freeDistance;

        if ($hasExcess && !$workOrder->customer_accepts_cost) {
            throw new \RuntimeException('El cliente debe aceptar el costo adicional para aprobar la verificación.');
        }

        // Cerrar la OT de verificación con su tiempo acumulado real
        $totalSeconds = (int) ($workOrder->accumulated_seconds ?? 0);
        if ($workOrder->started_at) {
            $totalSeconds += $workOrder->started_at->diffInSeconds(now());
        }

        $workOrder->update([
            'status' => 'completed',
            'completed_date' => now(),
            'accumulated_seconds' => $totalSeconds,
            'verification_price' => $price,
            'customer_accepts_cost' => $hasExcess ? true : $workOrder->customer_accepts_cost,
        ]);

        // Promover el ticket a la fase de contratación
        $ticket->update([
            'promotion_status' => 'promoted',
            'promoted_at' => now(),
            'contract_price_snapshot' => $price,
            'requires_contract' => true,
            'status' => 'in_progress',
            'contracts_escalated_at' => now(),
        ]);

        // Generar el contrato precargado (sin duplicar)
        $contract = Contract::where('ticket_id', $ticket->id)->first();
        if (!$contract) {
            $client = $ticket->client;
            $zoneId = $ticket->zone_id;
            $extraTvs = max(0, (int) ($workOrder->extra_tvs ?? 0));
            $fees = \App\Services\TvExtraFees::forZone($zoneId ?: null);

            $contract = Contract::create([
                'client_id' => $ticket->client_id,
                'ticket_id' => $ticket->id,
                'plan_id' => $ticket->plan_id,
                'zone_id' => $zoneId,
                'service_type' => 'instalacion',
                'installation_cost' => $price,
                'extra_tvs' => $extraTvs,
                'tv_install_fee' => $extraTvs * $fees['install_fee'],
                'monthly_extra_fee' => $extraTvs * ($fees['monthly_fee'] ?? 1),
                'installation_address' => $client?->installation_address ?? $client?->address,
                'latitude' => $workOrder->latitude ?? $client?->latitude,
                'longitude' => $workOrder->longitude ?? $client?->longitude,
                'contract_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Registrar cobros de TV extra precargados (si el técnico anotó alguna)
            if ($extraTvs > 0) {
                $contract->charges()->create([
                    'client_id' => $contract->client_id,
                    'type' => 'extra_tv',
                    'charge_type' => 'extra_tv',
                    'description' => "Instalación de TV extra x{$extraTvs}",
                    'amount' => $contract->tv_install_fee,
                    'is_recurring' => false,
                    'quantity' => $extraTvs,
                ]);
                $contract->charges()->create([
                    'client_id' => $contract->client_id,
                    'type' => 'extra_tv',
                    'charge_type' => 'extra_tv',
                    'description' => "Recargo mensual TV extra x{$extraTvs}",
                    'amount' => $contract->monthly_extra_fee,
                    'is_recurring' => true,
                    'recurring_period' => 'monthly',
                    'quantity' => $extraTvs,
                ]);
            }
        }

        try {
            TicketPromotedToContract::dispatch($ticket->ticket_code);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast de promoción omitido: ' . $e->getMessage());
        }

        return $contract;
    }

    /**
     * Rechaza la verificación (no factible / cliente no acepta el cobro):
     * completa la OT y cierra el ticket como resuelto con el motivo.
     */
    public function reject(WorkOrder $workOrder, string $reason): void
    {
        $ticket = $workOrder->ticket;
        if (!$ticket) {
            throw new \RuntimeException('La OT de verificación no tiene ticket asociado.');
        }

        $totalSeconds = (int) ($workOrder->accumulated_seconds ?? 0);
        if ($workOrder->started_at) {
            $totalSeconds += $workOrder->started_at->diffInSeconds(now());
        }

        $workOrder->update([
            'status' => 'completed',
            'completed_date' => now(),
            'accumulated_seconds' => $totalSeconds,
            'customer_accepts_cost' => false,
        ]);

        $ticket->update([
            'promotion_status' => 'rejected',
            'rejection_reason' => $reason,
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        app(SlaService::class)->evaluateSla($ticket->fresh());
    }
}
