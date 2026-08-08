<?php

namespace App\Livewire\Bodega;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Device;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionLog;
use App\Models\TechnicianInventory;
use App\Notifications\RequisitionStatusNotification;
use App\Services\InventoryService;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RequisitionBodegaIndex extends Component
{
    use WithPagination;

    public $selectedRequisition = null;
    public $branchAssignments = [];
    public $removedItems = [];
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $rejectionReason = '';
    public $approvalSummary = [];

    public $activeTab = 'pending';
    public $viewingHistoryId = null;
    public $viewingHistory = null;

    public $pendingSearch = '';
    public $historySearch = '';
    public $historyStatus = '';

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
        $this->approvalSummary = [];
    }

    public function confirmApprove()
    {
        $this->approvalSummary = [];
        $requisition = $this->selectedRequisition;

        foreach ($this->branchAssignments as $itemId => $assign) {
            $item = $requisition->items->firstWhere('id', $itemId);
            if (!$item) continue;

            $qty = (int) ($assign['quantity'] ?? 0);
            $product = Product::find($assign['product_id']);
            $isRemoved = in_array($itemId, $this->removedItems);

            $sourceBranch = null;
            $stockAvailable = 0;
            if ($assign['source_branch_id']) {
                $sourceBranch = Branch::find($assign['source_branch_id']);
                $branchInv = BranchInventory::where('branch_id', $assign['source_branch_id'])
                    ->where('product_id', $assign['product_id'])
                    ->first();
                $stockAvailable = $branchInv ? (int) $branchInv->allocated_quantity : 0;
            } else {
                $stockAvailable = $product ? (int) $product->current_stock : 0;
            }

            $deviceCount = 0;
            if ($qty > 0 && !$isRemoved && $product) {
                $dq = Device::where('product_id', $assign['product_id'])
                    ->whereNull('technician_id')
                    ->where('status', 'in_stock');
                if ($assign['source_branch_id']) {
                    $dq->where('branch_id', $assign['source_branch_id']);
                } else {
                    $dq->whereNull('branch_id');
                }
                $deviceCount = $dq->count();
            }

            $this->approvalSummary[] = [
                'item_id' => $itemId,
                'product_name' => $product?->name ?? '—',
                'requested_qty' => (int) $item->quantity_requested,
                'qty' => $qty,
                'removed' => $isRemoved,
                'inherited' => (bool) $item->is_inherited,
                'source_branch_name' => $sourceBranch?->name,
                'stock_available' => $stockAvailable,
                'device_count' => $deviceCount,
            ];
        }

        $this->showApproveModal = true;
    }

    public function approve()
    {
        $this->showApproveModal = false;

        DB::beginTransaction();
        try {
            // Bloquear la fila y verificar que siga pendiente (evita doble aprobación)
            $requisition = Requisition::with('items.product')
                ->where('id', $this->selectedRequisition->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$requisition) {
                throw new \Exception('La requisición ya fue procesada o no está pendiente.');
            }

            foreach ($this->branchAssignments as $itemId => $assign) {
                $item = $requisition->items->firstWhere('id', $itemId);
                if (!$item) continue;

                // Los ítems heredados ya están en el inventario del técnico:
                // solo se registran en la requisición, no se despachan de bodega.
                if ($item->is_inherited) continue;

                $qty = (int) ($assign['quantity'] ?? 0);

                // Validación estricta en servidor (nunca más de lo solicitado ni negativos)
                if ($qty < 0 || $qty > (int) $item->quantity_requested) {
                    throw new \Exception("Cantidad inválida para {$item->product?->name}: {$qty} (solicitado: {$item->quantity_requested}).");
                }

                if ($qty <= 0) continue;

                // Lock pesimista sobre el producto para evitar carreras de concurrencia
                $product = Product::where('id', $assign['product_id'])->lockForUpdate()->first();
                if (!$product) {
                    throw new \Exception('Producto no válido en la requisición.');
                }

                if ($assign['source_branch_id']) {
                    $branchInv = BranchInventory::where('branch_id', $assign['source_branch_id'])
                        ->where('product_id', $assign['product_id'])
                        ->lockForUpdate()
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

                $inventory = TechnicianInventory::firstOrNew([
                    'technician_id' => $requisition->technician_id,
                    'product_id' => $assign['product_id'],
                ]);
                $inventory->quantity_in_hand = ($inventory->quantity_in_hand ?? 0) + $qty;
                $inventory->save();

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

            // ── Historial de modificaciones realizadas por bodega ──
            $logEntries = [];
            foreach ($this->branchAssignments as $itemId => $assign) {
                $item = $requisition->items->firstWhere('id', $itemId);
                if (!$item || $item->is_inherited) continue;

                $originalName = $item->product?->name ?? 'Producto';
                $newProduct = Product::find($assign['product_id']);
                $qty = (int) ($assign['quantity'] ?? 0);

                if (in_array($itemId, $this->removedItems)) {
                    $logEntries[] = "Se quitó el producto {$originalName} de la requisición.";
                    continue;
                }
                if ((int) $assign['product_id'] !== (int) $item->product_id) {
                    $logEntries[] = "Se sustituyó {$originalName} por {$newProduct?->name}.";
                }
                if ((int) $item->quantity_requested !== $qty) {
                    $logEntries[] = "Se ajustó la cantidad de {$originalName}: {$item->quantity_requested} → {$qty}.";
                }
                if ($assign['source_branch_id']) {
                    $branchName = Branch::find($assign['source_branch_id'])?->name;
                    $logEntries[] = "Se asignó la sucursal de origen {$branchName} para {$originalName}.";
                }
            }

            foreach ($logEntries as $desc) {
                RequisitionLog::create([
                    'requisition_id' => $requisition->id,
                    'user_id' => Auth::id(),
                    'action' => 'modified',
                    'description' => $desc,
                ]);
            }

            $deliveredCount = 0;
            foreach ($this->branchAssignments as $itemId => $assign) {
                $item = $requisition->items->firstWhere('id', $itemId);
                if ($item && !$item->is_inherited && (int) ($assign['quantity'] ?? 0) > 0) {
                    $deliveredCount++;
                }
            }

            RequisitionLog::create([
                'requisition_id' => $requisition->id,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'description' => "Requisición aprobada por " . (auth()->user()->name ?? 'bodega') . ". {$deliveredCount} producto(s) entregado(s).",
            ]);

            $requisition->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'branch_id' => collect($this->branchAssignments)->first()['source_branch_id'] ?: null,
            ]);

            DB::commit();

            if ($technician = $requisition->technician) {
                $notification = new RequisitionStatusNotification($requisition, 'approved');
                $technician->notify($notification);
                broadcast(new BroadcastNotificationCreated($notification, $technician));
            }

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
        $this->validate([
            'rejectionReason' => 'required|string|min:5',
        ]);

        $this->selectedRequisition->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        RequisitionLog::create([
            'requisition_id' => $this->selectedRequisition->id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'description' => "Requisición rechazada por " . (auth()->user()->name ?? 'bodega') . ": {$this->rejectionReason}",
        ]);

        if ($technician = $this->selectedRequisition->technician) {
            $notification = new RequisitionStatusNotification($this->selectedRequisition, 'rejected', $this->rejectionReason);
            $technician->notify($notification);
            broadcast(new BroadcastNotificationCreated($notification, $technician));
        }

        $this->dispatch('show-toast', type: 'info', message: 'Requisición #' . $this->selectedRequisition->id . ' rechazada.');
        $this->back();
    }

    public function availableBranches($productId)
    {
        return Branch::whereHas('inventories', function ($q) use ($productId) {
            $q->where('product_id', $productId)->where('allocated_quantity', '>', 0);
        })->orderBy('name')->get();
    }

    // ==================== HISTORIAL ====================

    public function selectHistory($id)
    {
        $requisition = Requisition::with('technician', 'logs.user', 'items.product', 'branch')
            ->findOrFail($id);

        $this->ensureInitialLog($requisition);

        $this->viewingHistoryId = $id;
        $this->viewingHistory = $requisition->fresh(['logs.user', 'items.product', 'technician', 'branch']);
    }

    public function backToHistory()
    {
        $this->viewingHistoryId = null;
        $this->viewingHistory = null;
    }

    private function ensureInitialLog($requisition)
    {
        if ($requisition->logs()->exists()) {
            return;
        }

        $isRejected = $requisition->status === 'rejected';
        RequisitionLog::create([
            'requisition_id' => $requisition->id,
            'user_id' => $requisition->approver?->id ?? 1,
            'action' => $isRejected ? 'rejected' : 'approved',
            'description' => $isRejected
                ? "Requisición rechazada: " . ($requisition->rejection_reason ?: 'Sin motivo registrado.')
                : "Requisición aprobada.",
            'created_at' => $requisition->approved_at ?? $requisition->updated_at,
        ]);
    }

    public function render()
    {
        $requisitions = Requisition::with('technician', 'items.product', 'branch')
            ->where('status', 'pending')
            ->when($this->pendingSearch, fn($q) => $q->whereHas('technician', fn($t) => $t->where('name', 'like', '%' . $this->pendingSearch . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'pendingPage');

        $history = Requisition::with('technician', 'logs.user')
            ->withCount('items')
            ->whereIn('status', ['approved', 'rejected'])
            ->when($this->historySearch, fn($q) => $q->whereHas('technician', fn($t) => $t->where('name', 'like', '%' . $this->historySearch . '%')))
            ->when($this->historyStatus, fn($q) => $q->where('status', $this->historyStatus))
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'historyPage');

        $technicianInventory = null;
        if ($this->selectedRequisition) {
            $technicianInventory = TechnicianInventory::with('product')
                ->where('technician_id', $this->selectedRequisition->technician_id)
                ->get();
        }

        $allBranches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('livewire.bodega.requisition-bodega-index', compact(
            'requisitions', 'history', 'allBranches', 'technicianInventory'
        ))->layout('components.layouts.app');
    }
}
