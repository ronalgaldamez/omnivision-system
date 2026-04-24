<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Movement;
use App\Models\TechnicianRequest;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Procesar una entrada por compra o devolución que recalcula costo promedio
     */
    public function processPurchaseEntry(Product $product, $quantity, $cost, Movement $movement)
    {
        if ($product->is_obsolete || $product->is_floating) {
            $movement->unit_cost = 0;
            $movement->total_value = 0;
            $movement->save();
            return;
        }

        DB::transaction(function () use ($product, $quantity, $cost, $movement) {
            $currentQuantity = $product->current_stock;
            $currentValue = $product->total_value ?? 0;
            $newValue = $quantity * $cost;
            $totalQuantity = $currentQuantity + $quantity;

            if ($totalQuantity == 0) {
                $newAverage = 0;
                $newTotalValue = 0;
            } else {
                $newAverage = round(($currentValue + $newValue) / $totalQuantity, 4);
                $newTotalValue = round($totalQuantity * $newAverage, 2);
            }

            $product->current_stock = $totalQuantity;
            $product->average_cost = $newAverage;
            $product->total_value = $newTotalValue;
            $product->save();

            // Actualizar el movimiento con el costo y total final (puede haber redondeo)
            $movement->unit_cost = $cost;
            $movement->total_value = $newValue;
            $movement->save();
        });
    }

    /**
     * Procesar una salida (cualquier tipo que no sea entrada)
     */
    public function processExit(Product $product, $quantity, Movement $movement)
    {
        if ($product->is_obsolete || $product->is_floating) {
            $movement->unit_cost = 0;
            $movement->total_value = 0;
            $movement->save();
            return;
        }

        $averageCost = $product->average_cost ?? 0;
        $totalValue = $quantity * $averageCost;

        DB::transaction(function () use ($product, $quantity, $averageCost, $totalValue, $movement) {
            $product->current_stock -= $quantity;
            $product->total_value -= $totalValue;
            if ($product->current_stock == 0) {
                $product->average_cost = 0;
                $product->total_value = 0;
            }
            $product->save();

            $movement->unit_cost = $averageCost;
            $movement->total_value = $totalValue;
            $movement->save();
        });
    }

    /**
     * Procesar devolución de técnico (con regla de tolerancia)
     * NOTA: Ya no se usa porque las devoluciones de sobrante ahora se tratan como entradas que recalcular promedio.
     * Se mantiene por compatibilidad, pero no se llama desde ReturnForm.
     */
    public function processTechnicianReturn(TechnicianRequest $request, Movement $returnMovement)
    {
        // Este método ya no se usa en el flujo actual
        // Se deja para evitar errores si algún otro lugar lo llama
        $originalMovement = Movement::where('reference_type', 'technician_request')
            ->where('reference_id', $request->id)
            ->where('type', 'technician_out')
            ->first();

        $product = $returnMovement->product;

        if ($product->is_obsolete || $product->is_floating) {
            $returnMovement->unit_cost = 0;
            $returnMovement->total_value = 0;
            $returnMovement->save();
            return;
        }

        $isSameDay = $originalMovement && $originalMovement->created_at->isToday() && $returnMovement->created_at->isToday();

        if ($isSameDay) {
            $cost = $originalMovement->unit_cost;
            $totalValue = $returnMovement->quantity * $cost;
            DB::transaction(function () use ($product, $returnMovement, $cost, $totalValue) {
                $product->current_stock += $returnMovement->quantity;
                $product->total_value += $totalValue;
                if ($product->current_stock > 0) {
                    $product->average_cost = $product->total_value / $product->current_stock;
                }
                $product->save();

                $returnMovement->unit_cost = $cost;
                $returnMovement->total_value = $totalValue;
                $returnMovement->save();
            });
        } else {
            DB::transaction(function () use ($product, $returnMovement) {
                $product->current_stock += $returnMovement->quantity;
                if ($product->current_stock > 0) {
                    $product->average_cost = $product->total_value / $product->current_stock;
                }
                $product->save();

                $returnMovement->unit_cost = 0;
                $returnMovement->total_value = 0;
                $returnMovement->save();
            });
        }
    }
}