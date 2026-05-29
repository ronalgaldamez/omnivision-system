<?php

namespace App\Services;

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
}