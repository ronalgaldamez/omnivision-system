<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Movement;
use App\Models\TechnicianRequest;
use App\Services\InventoryService;

class RecalculateHistoricalInventory extends Command
{
    protected $signature = 'inventory:recalculate';
    protected $description = 'Recalcula el costo promedio ponderado y valores de inventario desde cero';

    public function handle()
    {
        $products = Product::where('is_obsolete', false)->where('is_floating', false)->get();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $inventoryService = new InventoryService();

        foreach ($products as $product) {
            // Resetear valores del producto
            $product->current_stock = 0;
            $product->average_cost = 0;
            $product->total_value = 0;
            $product->save();

            $movements = Movement::where('product_id', $product->id)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($movements as $movement) {
                if ($movement->type == 'entry') {
                    // Entrada por compra (asumimos que el unit_cost es el costo de compra)
                    $inventoryService->processPurchaseEntry($product, $movement->quantity, $movement->unit_cost, $movement);
                } elseif (in_array($movement->type, ['exit', 'technician_out', 'return_to_supplier', 'damage'])) {
                    $inventoryService->processExit($product, $movement->quantity, $movement);
                } elseif ($movement->type == 'technician_return') {
                    $request = TechnicianRequest::find($movement->reference_id);
                    if ($request) {
                        $inventoryService->processTechnicianReturn($request, $movement);
                    } else {
                        $this->warn("Movimiento #{$movement->id} sin referencia de técnico, se omite.");
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Recálculo completado.');
    }
}