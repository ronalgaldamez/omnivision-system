<?php

namespace App\Livewire\Bodega;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Device;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\TechnicianInventory;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RequisitionBodegaIndex extends Component
{
    public $selectedRequisition = null;
    public $branchAssignments = [];
    public $removedItems = [];
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $rejectionReason = '';

    public $changingItemId = null;
    public $showSubstituteModal = false;
    public $substituteSearch = '';
    public $substituteResults = [];
    public $substituteList = [];
    public $substituteListSearch = '';

    public function selectRequisition($id)
    {
        $this->selectedRequisition = Requisition::with('items.product', 'technician', 'workOrders')
            ->findOrFail($id);

        $this->branchAssignments = [];
        $this->removedItems = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->branchAssignments[$item->id] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity_requested,
                'source_branch_id' => '',
            ];
        }
    }

    public function removeItem($itemId)
    {
        $this->removedItems[] = $itemId;
        $this->branchAssignments[$itemId]['quantity'] = 0;
        $this->dispatch('show-toast', type: 'info', message: 'Producto quitado de la requisición.');
    }

    public function restoreItem($itemId)
    {
        $this->removedItems = array_filter($this->removedItems, fn($id) => $id != $itemId);
        $item = $this->selectedRequisition->items->firstWhere('id', $itemId);
        if ($item) {
            $this->branchAssignments[$itemId]['quantity'] = $item->quantity_requested;
        }
        $this->dispatch('show-toast', type: 'info', message: 'Producto restaurado.');
    }

    // ==================== SUSTITUCIÓN DE PRODUCTO ====================
    public function openSubstituteModal($itemId)
    {
        $this->changingItemId = $itemId;
        $this->substituteSearch = '';
        $this->substituteResults = [];
        $this->substituteList = Product::orderBy('name')->take(50)->get();
        $this->substituteListSearch = '';
        $this->showSubstituteModal = true;
    }

    public function closeSubstituteModal()
    {
        $this->showSubstituteModal = false;
        $this->changingItemId = null;
        $this->substituteSearch = '';
        $this->substituteResults = [];
        $this->substituteList = [];
        $this->substituteListSearch = '';
    }

    public function updatedSubstituteSearch()
    {
        if (strlen($this->substituteSearch) >= 2) {
            $this->substituteResults = Product::where('name', 'like', '%'.$this->substituteSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->substituteSearch.'%')
                ->limit(10)->get();
        } else {
            $this->substituteResults = [];
        }
    }

    public function updatedSubstituteListSearch()
    {
        if (strlen($this->substituteListSearch) >= 2) {
            $this->substituteList = Product::where('name', 'like', '%'.$this->substituteListSearch.'%')
                ->orWhere('sku', 'like', '%'.$this->substituteListSearch.'%')
                ->orderBy('name')->take(50)->get();
        } else {
            $this->substituteList = Product::orderBy('name')->take(50)->get();
        }
    }

    public function selectSubstitute($productId)
    {
        $product = Product::find($productId);
        if (!$product || !$this->changingItemId) return;

        $this->branchAssignments[$this->changingItemId]['product_id'] = $product->id;
        $this->dispatch('show-toast', type: 'info', message: "Producto cambiado a: {$product->name}");
        $this->closeSubstituteModal();
    }

    public function back()
    {
        $this->selectedRequisition = null;
        $this->branchAssignments = [];
        $this->removedItems = [];
        $this->showApproveModal = false;
        $this->showRejectModal = false;
    }

    public function confirmApprove()
    {
        $this->showApproveModal = true;
    }

    public function approve()
    {
        $requisition = $this->selectedRequisition;

        DB::beginTransaction();
        try {
            foreach ($this->branchAssignments as $itemId => $assign) {
                $item = $requisition->items->firstWhere('id', $itemId);
                if (!$item) continue;

                $product = Product::find($assign['product_id']);
                $qty = (int) ($assign['quantity'] ?? 0);

                if ($qty <= 0) continue;

                if ($assign['source_branch_id']) {
                    $branchInv = BranchInventory::where('branch_id', $assign['source_branch_id'])
                        ->where('product_id', $assign['product_id'])
                        ->first();

                    if (!$branchInv || $branchInv->allocated_quantity < $qty) {
                        throw new \Exception("Stock insuficiente en la sucursal seleccionada para {$product->name}");
                    }

                    $branchInv->decrement('allocated_quantity', $qty);
                } else {
                    if ($product->current_stock < $qty) {
                        throw new \Exception("Stock general insuficiente para {$product->name}. Disponible: {$product->current_stock}");
                    }
                    $product->decrement('current_stock', $qty);
                }

                Movement::create([
                    'product_id' => $assign['product_id'],
                    'type' => 'requisition_out',
                    'quantity' => $qty,
                    'unit_cost' => $product->average_cost ?? 0,
                    'total_value' => ($qty * ($product->average_cost ?? 0)),
                    'description' => 'Requisición #' . $requisition->id . ' (aprobada)',
                    'user_id' => Auth::id(),
                    'reference_type' => 'requisition',
                    'reference_id' => $requisition->id,
                    'branch_id' => $assign['source_branch_id'] ?: null,
                ]);

                TechnicianInventory::updateOrCreate(
                    ['technician_id' => $requisition->technician_id, 'product_id' => $assign['product_id']],
                    ['quantity_in_hand' => DB::raw('COALESCE(quantity_in_hand, 0) + ' . $qty)]
                );

                $deviceQuery = Device::where('product_id', $assign['product_id'])
                    ->whereNull('technician_id')
                    ->where('status', 'in_stock');

                if ($assign['source_branch_id']) {
                    $deviceQuery->where('branch_id', $assign['source_branch_id']);
                } else {
                    $deviceQuery->whereNull('branch_id');
                }

                $devices = $deviceQuery->take($qty)->get();

                foreach ($devices as $device) {
                    $device->update([
                        'technician_id' => $requisition->technician_id,
                        'status' => 'assigned',
                        'assigned_at' => now(),
                    ]);
                }
            }

            $requisition->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'branch_id' => collect($this->branchAssignments)->first()['source_branch_id'] ?: null,
            ]);

            DB::commit();
            $this->dispatch('show-toast', type: 'success', message: 'Requisición #' . $requisition->id . ' aprobada.');
            $this->back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function confirmReject()
    {
        $this->showRejectModal = true;
    }

    public function reject()
    {
        $this->selectedRequisition->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->dispatch('show-toast', type: 'info', message: 'Requisición #' . $this->selectedRequisition->id . ' rechazada.');
        $this->back();
    }

    public function availableBranches($productId)
    {
        return Branch::whereHas('inventories', function ($q) use ($productId) {
            $q->where('product_id', $productId)->where('allocated_quantity', '>', 0);
        })->orderBy('name')->get();
    }

    public function render()
    {
        $requisitions = Requisition::with('technician', 'items.product', 'branch')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $allBranches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('livewire.bodega.requisition-bodega-index', compact('requisitions', 'allBranches'))
            ->layout('components.layouts.app');
    }
}
