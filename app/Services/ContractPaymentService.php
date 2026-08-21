<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractCharge;
use App\Models\WorkOrder;

/**
 * Centraliza el cálculo y registro de abonos/cuotas del contrato.
 * - Abono proporcional: cuota ÷ días del mes × días desde la instalación hasta la fecha de pago.
 */
class ContractPaymentService
{
    /**
     * Retorna la fecha de instalación real desde la OT de instalación del ticket, sino null.
     */
    public function installationDate(Contract $contract): ?\Illuminate\Support\Carbon
    {
        if ($contract->ticket_id) {
            $wo = WorkOrder::where('ticket_id', $contract->ticket_id)
                ->where('service_type', 'instalacion')
                ->first();
            if ($wo && $wo->completed_date) {
                return $wo->completed_date;
            }
        }
        return null;
    }

    /**
     * Calcula el abono proporcional del contrato usando la fecha de instalación.
     */
    public function calculateAbono(Contract $contract): ?array
    {
        $inst = $this->installationDate($contract);
        return $contract->abonoProporcional($inst);
    }

    /**
     * Registra el abono proporcional como cargo del contrato.
     */
    public function registerAbono(Contract $contract): ContractCharge
    {
        $calc = $this->calculateAbono($contract);
        $base = $contract->monthlyTotal();

        $charge = new ContractCharge([
            'contract_id' => $contract->id,
            'client_id' => $contract->client_id,
            'type' => 'abono',
            'charge_type' => 'abono',
            'description' => "Abono proporcional por {$calc['days']} días de servicio",
            'amount' => $calc['charge'],
            'base_amount' => $base,
            'is_recurring' => false,
            'quantity' => 1,
            'days' => $calc['days'],
            'applied_at' => now(),
        ]);
        $charge->save();

        return $charge;
    }

    /**
     * Registra una cuota completa mensual.
     */
    public function registerCuota(Contract $contract, ?\Illuminate\Support\Carbon $periodStart = null): ContractCharge
    {
        $amount = $contract->monthlyTotal();

        $charge = new ContractCharge([
            'contract_id' => $contract->id,
            'client_id' => $contract->client_id,
            'type' => 'cuota',
            'charge_type' => 'cuota',
            'description' => "Cuota mensual completa",
            'amount' => $amount,
            'base_amount' => $amount,
            'is_recurring' => true,
            'recurring_period' => 'monthly',
            'quantity' => 1,
            'days' => null,
            'applied_at' => now(),
        ]);
        $charge->save();

        return $charge;
    }
}
