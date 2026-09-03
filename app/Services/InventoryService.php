<?php

namespace App\Services;

use App\Models\CompanyProductInventory;
use App\Models\Product;
use App\Models\Movement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
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

            $movement->unit_cost = $cost;
            $movement->total_value = $newValue;
            $movement->save();
        });
    }

    /**
     * Entrada por compra valorizada al nivel de la empresa.
     * Actualiza el costo promedio ponderado de la empresa (company_product_inventories)
     * y mantiene la contabilidad global del producto para no romper los flujos existentes.
     */
    public function processCompanyPurchaseEntry(int $companyId, Product $product, $quantity, $cost, Movement $movement)
    {
        if ($product->is_obsolete || $product->is_floating) {
            $movement->unit_cost = 0;
            $movement->total_value = 0;
            $movement->save();
            return;
        }

        DB::transaction(function () use ($companyId, $product, $quantity, $cost, $movement) {
            $record = CompanyProductInventory::firstOrCreate(
                ['company_id' => $companyId, 'product_id' => $product->id],
                ['quantity' => 0, 'average_cost' => 0, 'total_value' => 0]
            );

            $currentQty = (float) $record->quantity;
            $currentValue = (float) $record->total_value;
            $newValue = $quantity * $cost;
            $totalQty = $currentQty + $quantity;

            if ($totalQty == 0) {
                $newAverage = 0;
                $newTotalValue = 0;
            } else {
                $newAverage = round(($currentValue + $newValue) / $totalQty, 4);
                $newTotalValue = round($totalQty * $newAverage, 2);
            }

            $record->quantity = $totalQty;
            $record->average_cost = $newAverage;
            $record->total_value = $newTotalValue;
            $record->save();

            // Mantener la contabilidad global del producto (compatibilidad hasta Fase C)
            $currentGlobalQty = $product->current_stock;
            $currentGlobalValue = $product->total_value ?? 0;
            $totalGlobalQty = $currentGlobalQty + $quantity;
            $globalAverage = $totalGlobalQty > 0
                ? round(($currentGlobalValue + $newValue) / $totalGlobalQty, 4)
                : 0;
            $product->current_stock = $totalGlobalQty;
            $product->average_cost = $globalAverage;
            $product->total_value = round($totalGlobalQty * $globalAverage, 2);
            $product->save();

            $movement->unit_cost = $cost;
            $movement->total_value = $newValue;
            $movement->save();
        });
    }

    /**
     * Venta entre empresas: la empresa vendedora reduce su cantidad y la
     * compradora ingresa la cantidad recalculando su costo promedio ponderado.
     * NO toca el stock global del producto (el total del sistema no cambia).
     */
    public function processCompanySale(int $sellerCompanyId, int $buyerCompanyId, Product $product, $quantity, $cost)
    {
        DB::transaction(function () use ($sellerCompanyId, $buyerCompanyId, $product, $quantity, $cost) {
            // ── Empresa vendedora: reduce cantidad ──
            $seller = CompanyProductInventory::where('company_id', $sellerCompanyId)
                ->where('product_id', $product->id)
                ->first();

            if ($seller) {
                $newQty = max(0, (float) $seller->quantity - $quantity);
                $seller->quantity = $newQty;
                if ($newQty <= 0) {
                    $seller->quantity = 0;
                    $seller->average_cost = 0;
                    $seller->total_value = 0;
                } else {
                    $seller->total_value = round($newQty * (float) $seller->average_cost, 2);
                }
                $seller->save();
            }

            // ── Empresa compradora: ingresa cantidad y recalcula promedio ──
            $buyer = CompanyProductInventory::firstOrCreate(
                ['company_id' => $buyerCompanyId, 'product_id' => $product->id],
                ['quantity' => 0, 'average_cost' => 0, 'total_value' => 0]
            );

            $currentQty = (float) $buyer->quantity;
            $currentValue = (float) $buyer->total_value;
            $newValue = $quantity * $cost;
            $totalQty = $currentQty + $quantity;

            if ($totalQty <= 0) {
                $newAverage = 0;
                $newTotalValue = 0;
            } else {
                $newAverage = round(($currentValue + $newValue) / $totalQty, 4);
                $newTotalValue = round($totalQty * $newAverage, 2);
            }

            $buyer->quantity = $totalQty;
            $buyer->average_cost = $newAverage;
            $buyer->total_value = $newTotalValue;
            $buyer->save();
        });
    }

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
            if ($product->current_stock <= 0) {
                $product->current_stock = 0;
                $product->average_cost = 0;
                $product->total_value = 0;
            }
            $product->save();

            $movement->unit_cost = $averageCost;
            $movement->total_value = $totalValue;
            $movement->save();
        });
    }
}