<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KardexExport;
use App\Models\Product;
use App\Models\Movement;
use App\Models\Branch;

class KardexIndex extends Component
{
    use WithPagination;

    public $product_id = '';
    public $type = '';
    public $date_from = '';
    public $date_to = '';
    public $branch_id = '';
    public $movementPage = 1;

    public $productSearch = '';
    public $productResults = [];
    public $showProductModal = false;
    public $productList = [];
    public $productListSearch = '';

    protected $queryString = ['product_id', 'type', 'date_from', 'date_to', 'branch_id', 'movementPage'];

    public function updated($property, $value)
    {
        if (in_array($property, ['type', 'date_from', 'date_to', 'branch_id', 'product_id'])) {
            $this->movementPage = 1;
        }
    }

    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) >= 2) {
            $this->productResults = Product::where('name', 'like', '%' . $this->productSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($id, $name)
    {
        $this->product_id = $id;
        $this->productSearch = $name;
        $this->productResults = [];
    }

    public function clearProduct()
    {
        $this->product_id = '';
        $this->productSearch = '';
        $this->productResults = [];
    }

    public function openProductModal()
    {
        $this->productListSearch = '';
        $this->productList = Product::orderBy('name')->take(50)->get();
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->productListSearch = '';
        $this->productList = [];
    }

    public function updatedProductListSearch()
    {
        if (strlen($this->productListSearch) >= 2) {
            $this->productList = Product::where('name', 'like', '%' . $this->productListSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productListSearch . '%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->productList = Product::orderBy('name')->take(50)->get();
        }
    }

    public function selectProductFromList($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->selectProduct($product->id, $product->name . ' (' . $product->sku . ')');
            $this->closeProductModal();
        }
    }

    public function setDateRange($preset)
    {
        $this->movementPage = 1;

        switch ($preset) {
            case 'this_month':
                $this->date_from = now()->startOfMonth()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'last_quarter':
                $this->date_from = now()->subMonths(3)->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'this_year':
                $this->date_from = now()->startOfYear()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            default:
                $this->date_from = '';
                $this->date_to = '';
                break;
        }
    }

    public function exportKardex()
    {
        if (!$this->product_id) {
            return;
        }

        [$items, $finalQty, $finalValue] = $this->kardexMovements();
        $product = Product::find($this->product_id);
        $consolidated = (object) ['total_stock' => $finalQty, 'total_value' => $finalValue];

        return Excel::download(
            new KardexExport($items, $consolidated),
            'kardex_' . ($product?->sku ?? $this->product_id) . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function printKardex()
    {
        if (!$this->product_id) {
            return;
        }

        [$items, $finalQty, $finalValue] = $this->kardexMovements();
        $product = Product::with('category')->find($this->product_id);
        $consolidated = (object) ['total_stock' => $finalQty, 'total_value' => $finalValue];

        $pdf = Pdf::loadView('pdf.kardex', compact('product', 'items', 'consolidated'))
            ->setPaper('a4', 'landscape');

        $content = $pdf->output();
        $tempPath = tempnam(sys_get_temp_dir(), 'kardex_') . '.pdf';
        file_put_contents($tempPath, $content);

        return response()->download($tempPath, 'kardex_' . ($product?->sku ?? $this->product_id) . '.pdf')
            ->deleteFileAfterSend(true);
    }

    /**
     * Movimientos filtrados del producto con su balance calculado (stream completo).
     * Retorna [items, cantidadFinal, valorFinal].
     */
    private function kardexMovements()
    {
        $activeBranchId = auth()->user()->activeBranchId();
        $effectiveBranchId = $this->branch_id ?: $activeBranchId;

        $movements = Movement::where('product_id', $this->product_id)
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->date_from, fn($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->when($effectiveBranchId, fn($q) => $q->where('branch_id', $effectiveBranchId))
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'created_at', 'type', 'quantity', 'unit_cost', 'branch_id']);

        $balanceQty = 0;
        $balanceValue = 0;
        $balanceAvgCost = 0;

        $items = $this->processMovements($movements, $balanceQty, $balanceValue, $balanceAvgCost, $effectiveBranchId, 1);

        return [$items, $balanceQty, $balanceValue];
    }

    public function render()
    {
        $activeBranchId = auth()->user()->activeBranchId();
        $effectiveBranchId = $this->branch_id ?: $activeBranchId;
        $activeBranch = $effectiveBranchId ? Branch::find($effectiveBranchId) : null;
        $canManageBranches = auth()->user()->can('access_admin');

        // Vista general: lista paginada de productos (sin cargar movimientos)
        if (!$this->product_id) {
            $products = Product::with('category')
                ->when(strlen($this->productSearch) >= 2, fn($q) => $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->productSearch . '%')
                        ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
                }))
                ->orderBy('name')
                ->paginate(25, ['*'], 'productsPage');

            return view('livewire.inventory.kardex.index', compact('products', 'activeBranch'))
                ->layout('components.layouts.app');
        }

        // Kardex individual: stream completo filtrado (para balance consolidado) + página visible
        [$allItems, $finalQty, $finalValue] = $this->kardexMovements();
        $consolidated = (object) ['total_stock' => $finalQty, 'total_value' => $finalValue];

        $totalMovements = count($allItems);
        $perPage = 30;
        $lastPage = max(1, (int) ceil($totalMovements / $perPage));
        $this->movementPage = min(max((int) $this->movementPage, 1), $lastPage);
        $offset = ($this->movementPage - 1) * $perPage;
        $items = array_slice($allItems, $offset, $perPage);

        $product = Product::with('category')->find($this->product_id);
        $branches = Branch::orderBy('name')->get();

        return view('livewire.inventory.kardex.index', compact(
            'items', 'consolidated', 'activeBranch', 'product', 'branches', 'canManageBranches',
            'totalMovements', 'perPage', 'offset'
        ))->layout('components.layouts.app');
    }

    /**
     * Procesa movimientos y calcula el balance con costo promedio ponderado.
     * El estado ($balanceQty/$balanceValue/$balanceAvgCost) se pasa por referencia.
     */
    private function processMovements($movements, &$balanceQty, &$balanceValue, &$balanceAvgCost, $activeBranchId, $startLine = 1)
    {
        $items = [];

        foreach ($movements as $index => $mov) {
            $item = clone $mov;
            $item->line_number = $startLine + $index;

            $isEntry = in_array($mov->type, ['entry', 'technician_return']);
            $isExit = in_array($mov->type, ['exit', 'technician_out', 'damage', 'return_to_supplier', 'requisition_out']);
            $isAllocation = $mov->type === 'branch_allocation';

            if ($isEntry) {
                $entryCost = $mov->unit_cost ?: 0;
                $newTotalQty = $balanceQty + $mov->quantity;
                $newTotalValue = $balanceValue + ($entryCost * $mov->quantity);
                $newAvgCost = ($newTotalQty > 0) ? $newTotalValue / $newTotalQty : 0;

                $item->entry_qty = $mov->quantity;
                $item->entry_cost = $entryCost;
                $item->entry_total = $entryCost * $mov->quantity;
                $item->exit_qty = null;
                $item->exit_cost = null;
                $item->exit_total = null;

                $balanceQty = $newTotalQty;
                $balanceValue = $newTotalValue;
                $balanceAvgCost = $newAvgCost;
            } elseif ($isAllocation) {
                if ($activeBranchId) {
                    $entryCost = $mov->unit_cost ?: 0;
                    $newTotalQty = $balanceQty + $mov->quantity;
                    $newTotalValue = $balanceValue + ($entryCost * $mov->quantity);
                    $newAvgCost = ($newTotalQty > 0) ? $newTotalValue / $newTotalQty : 0;

                    $item->entry_qty = $mov->quantity;
                    $item->entry_cost = $entryCost;
                    $item->entry_total = $entryCost * $mov->quantity;
                    $item->exit_qty = null;
                    $item->exit_cost = null;
                    $item->exit_total = null;

                    $balanceQty = $newTotalQty;
                    $balanceValue = $newTotalValue;
                    $balanceAvgCost = $newAvgCost;
                } else {
                    $exitCost = $balanceAvgCost;
                    $exitTotal = $exitCost * $mov->quantity;

                    $item->entry_qty = null;
                    $item->entry_cost = null;
                    $item->entry_total = null;
                    $item->exit_qty = $mov->quantity;
                    $item->exit_cost = $exitCost;
                    $item->exit_total = $exitTotal;

                    $balanceQty -= $mov->quantity;
                    $balanceValue -= $exitTotal;
                    $balanceAvgCost = ($balanceQty > 0) ? $balanceValue / $balanceQty : 0;
                }
            } elseif ($isExit) {
                $exitCost = $balanceAvgCost;
                $exitTotal = $exitCost * $mov->quantity;

                $item->entry_qty = null;
                $item->entry_cost = null;
                $item->entry_total = null;
                $item->exit_qty = $mov->quantity;
                $item->exit_cost = $exitCost;
                $item->exit_total = $exitTotal;

                $balanceQty -= $mov->quantity;
                $balanceValue -= $exitTotal;
                $balanceAvgCost = ($balanceQty > 0) ? $balanceValue / $balanceQty : 0;
            } else {
                $item->entry_qty = null;
                $item->entry_cost = null;
                $item->entry_total = null;
                $item->exit_qty = null;
                $item->exit_cost = null;
                $item->exit_total = null;
            }

            $item->balance_qty = $balanceQty;
            $item->balance_cost = $balanceAvgCost;
            $item->balance_total = $balanceValue;

            $items[] = $item;
        }

        return $items;
    }
}
