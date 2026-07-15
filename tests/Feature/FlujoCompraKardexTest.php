<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoCompraKardexTest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_completo_compra_entrada_kardex()
    {
        // ======================================================================
        // 1. CREAR DATOS CON FACTORIES (2 líneas cada uno)
        // ======================================================================
        $usuario  = User::factory()->create();
        $proveedor = Supplier::factory()->create();

        $producto = Product::factory()->create([
            'current_stock' => 0,
            'average_cost'  => 0,
            'total_value'   => 0,
        ]);

        // ======================================================================
        // 2. SIMULAR UNA COMPRA (como se haría en el sistema real)
        // ======================================================================
        $compra = Purchase::factory()->create([
            'supplier_id'    => $proveedor->id,
            'user_id'        => $usuario->id,
            'invoice_number' => 'FAC-001',
            'subtotal'       => 1500.00,
            'iva_amount'     => 195.00,
            'total'          => 1695.00,
        ]);

        // Item de la compra: 10 unidades a $50 c/u
        $item = PurchaseItem::factory()->create([
            'purchase_id' => $compra->id,
            'product_id'  => $producto->id,
            'quantity'    => 10,
            'unit_cost'   => 50.00,
        ]);

        // ======================================================================
        // 3. CREAR EL MOVIMIENTO DE ENTRADA (lo que hace el sistema al recibir)
        // ======================================================================
        $movimiento = Movement::factory()->entry()->create([
            'product_id'     => $producto->id,
            'user_id'        => $usuario->id,
            'type'           => 'entry',
            'quantity'       => $item->quantity,
            'reference_type' => 'purchase',
            'reference_id'   => $compra->id,
        ]);

        // ======================================================================
        // 4. EJECUTAR LA LÓGICA DEL KARDEX (costo promedio ponderado)
        // ======================================================================
        $service = new InventoryService();
        $service->processPurchaseEntry(
            $producto,
            $item->quantity,
            $item->unit_cost,
            $movimiento
        );

        // ======================================================================
        // 5. VERIFICAR QUE EL KARDEX CALCULÓ BIEN
        // ======================================================================
        $producto->refresh();
        $movimiento->refresh();

        // Stock final = 0 + 10 = 10
        $this->assertEquals(10, $producto->current_stock);

        // Costo promedio = (0 + 500) / 10 = 50
        $this->assertEquals(50.00, (float) $producto->average_cost);

        // Valor total = 10 * 50 = 500
        $this->assertEquals(500.00, (float) $producto->total_value);

        // El movimiento guardó el costo unitario
        $this->assertEquals(50.00, (float) $movimiento->unit_cost);

        // ======================================================================
        // 6. SEGUNDA COMPRA — Verificar promedio ponderado
        // ======================================================================
        $segundoItem = PurchaseItem::factory()->create([
            'purchase_id' => $compra->id,
            'product_id'  => $producto->id,
            'quantity'    => 5,
            'unit_cost'   => 80.00, // más caro
        ]);

        $segundoMov = Movement::factory()->entry()->create([
            'product_id' => $producto->id,
            'user_id'    => $usuario->id,
            'type'       => 'entry',
            'quantity'   => $segundoItem->quantity,
        ]);

        $service->processPurchaseEntry(
            $producto,
            $segundoItem->quantity,
            $segundoItem->unit_cost,
            $segundoMov
        );

        $producto->refresh();

        // Stock = 10 + 5 = 15
        $this->assertEquals(15, $producto->current_stock);

        // Promedio = (500 + 400) / 15 = 60.00
        $this->assertEquals(60.00, (float) $producto->average_cost);

        // ======================================================================
        // 7. SALIDA DE INVENTARIO — Verificar que descuenta bien
        // ======================================================================
        $salida = Movement::factory()->exit()->create([
            'product_id' => $producto->id,
            'user_id'    => $usuario->id,
            'type'       => 'exit',
            'quantity'   => 3,
        ]);

        $service->processExit($producto, 3, $salida);

        $producto->refresh();

        // Stock = 15 - 3 = 12
        $this->assertEquals(12, $producto->current_stock);

        // Valor total = 900 - (3 * 60) = 720
        $this->assertEquals(720.00, (float) $producto->total_value);

        // Costo promedio se mantiene en 60
        $this->assertEquals(60.00, (float) $producto->average_cost);
    }

    public function test_producto_obsoleto_no_afecta_inventario()
    {
        $producto = Product::factory()->obsolete()->create([
            'current_stock' => 5,
        ]);
        $movimiento = Movement::factory()->entry()->create([
            'product_id' => $producto->id,
            'quantity'   => 10,
        ]);

        $service = new InventoryService();
        $service->processPurchaseEntry($producto, 10, 50, $movimiento);

        $producto->refresh();

        // Producto obsoleto → el stock NO cambia
        $this->assertEquals(5, $producto->current_stock);
    }
}
