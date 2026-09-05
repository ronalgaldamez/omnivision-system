<?php

namespace App\Livewire\Bodega;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Quotation;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationIndex extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public $rejectionReason = '';
    public $rejectingId = null;
    public $showRejectModal = false;

    // Patrón canónico de confirmación (AGENTS)
    public $confirmingAction = null;
    public $confirmingId = null;

    private function confirmableAction(string $action, $id): bool
    {
        $quotation = Quotation::findOrFail($id);

        $checks = [
            'approve' => ['status' => 'pending', 'perm' => 'approve quotations', 'msg' => 'Solo se pueden aprobar cotizaciones pendientes.'],
            'pay' => ['status' => 'approved', 'perm' => 'pay quotations', 'msg' => 'Solo se pueden pagar cotizaciones aprobadas.'],
            'receive' => ['status' => 'paid', 'perm' => null, 'msg' => 'Solo se pueden recibir cotizaciones pagadas.'],
        ];

        $check = $checks[$action] ?? null;
        if (! $check) {
            $this->dispatch('show-toast', type: 'error', message: 'Acción no válida.');
            return false;
        }

        if ($quotation->status !== $check['status']) {
            $this->dispatch('show-toast', type: 'error', message: $check['msg']);
            return false;
        }

        if ($check['perm'] && auth()->user()->cannot($check['perm'])) {
            $this->dispatch('show-toast', type: 'error', message: 'No tenés permiso para esta acción.');
            return false;
        }

        if ($action === 'receive' && $quotation->purchase_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Esta cotización ya fue recibida.');
            return false;
        }

        return true;
    }

    public function confirmApprove($id)
    {
        if (! $this->confirmableAction('approve', $id)) {
            return;
        }
        $this->confirmingAction = 'approve';
        $this->confirmingId = $id;
    }

    public function confirmPay($id)
    {
        if (! $this->confirmableAction('pay', $id)) {
            return;
        }
        $this->confirmingAction = 'pay';
        $this->confirmingId = $id;
    }

    public function confirmReceive($id)
    {
        if (! $this->confirmableAction('receive', $id)) {
            return;
        }
        $this->confirmingAction = 'receive';
        $this->confirmingId = $id;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'approve') {
            $this->approve($this->confirmingId);
        } elseif ($this->confirmingAction === 'pay') {
            $this->markPaid($this->confirmingId);
        } elseif ($this->confirmingAction === 'receive') {
            $this->receive($this->confirmingId);
        } elseif ($this->confirmingAction === 'delete_draft') {
            $this->deleteDraft($this->confirmingId);
        }

        $this->cancelConfirmation();
    }

    public function askDeleteDraft($id)
    {
        $quotation = Quotation::find($id);
        if (! $quotation || $quotation->status !== 'draft' || (int) $quotation->created_by !== (int) auth()->id()) {
            $this->dispatch('show-toast', type: 'error', message: 'Este borrador no está disponible.');
            return;
        }

        $this->confirmingAction = 'delete_draft';
        $this->confirmingId = $id;
    }

    public function deleteDraft($id)
    {
        $quotation = Quotation::find($id);
        if ($quotation && $quotation->status === 'draft' && (int) $quotation->created_by === (int) auth()->id()) {
            $quotation->delete(); // items se eliminan en cascada
            $this->dispatch('show-toast', type: 'success', message: 'Borrador eliminado.');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'No se pudo eliminar el borrador.');
        }
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function approve($id)
    {
        $quotation = Quotation::findOrFail($id);

        $quotation->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        $this->dispatch('show-toast', type: 'success', message: "Cotización {$quotation->code} aprobada.");
    }

    public function openReject($id)
    {
        $this->rejectingId = $id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function reject()
    {
        $quotation = Quotation::findOrFail($this->rejectingId);

        if ($quotation->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden rechazar cotizaciones pendientes.');
            return;
        }
        if (auth()->user()->cannot('approve quotations')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tenés permiso para rechazar cotizaciones.');
            return;
        }
        if (empty(trim($this->rejectionReason))) {
            $this->dispatch('show-toast', type: 'error', message: 'Indicá el motivo del rechazo.');
            return;
        }

        $quotation->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);
        $this->showRejectModal = false;
        $this->rejectingId = null;
        $this->rejectionReason = '';
        $this->dispatch('show-toast', type: 'success', message: "Cotización {$quotation->code} rechazada.");
    }

    public function markPaid($id)
    {
        $quotation = Quotation::findOrFail($id);

        $quotation->update([
            'status' => 'paid',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ]);
        $this->dispatch('show-toast', type: 'success', message: "Cotización {$quotation->code} pagada. Se espera la recepción del producto.");
    }

    /**
     * Recibir la cotización: genera la compra y entra el stock.
     */
    public function receive($id)
    {
        $quotation = Quotation::with('items', 'supplier', 'branch')->findOrFail($id);

        DB::beginTransaction();
        try {
            $invoice = $quotation->code;
            $purchase = Purchase::create([
                'supplier_id' => $quotation->supplier_id,
                'branch_id' => $quotation->branch_id,
                'invoice_number' => $invoice,
                'purchase_date' => now(),
                'notes' => 'Generada desde cotización ' . $quotation->code,
                'user_id' => Auth::id(),
                'subtotal' => $quotation->subtotal,
                'iva_amount' => $quotation->iva_amount,
                'total' => $quotation->total,
                'include_iva' => true,
            ]);

            $inventoryService = app(InventoryService::class);

            foreach ($quotation->items as $item) {
                $cost = (float) $item->unit_cost;
                $branch = $quotation->branch;

                // Si es un producto propuesto (no existía), se materializa ahora que llegó físicamente
                if ($item->isPending()) {
                    // El hook creating de Product asigna el SKU automáticamente
                    // (generateUniqueSku): no hardcodear PROD- + max(id)+1, frágil ante
                    // imports, borrados o concurrencia.
                    $product = \App\Models\Product::create([
                        'name' => $item->pending_name,
                        'unit_of_measure' => $item->pending_unit ?? 'unidad',
                        'category_id' => $item->pending_category_id,
                        'current_stock' => 0,
                        'stock_min' => 0,
                    ]);
                    $item->update(['product_id' => $product->id]);
                } else {
                    $product = $item->product;
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => (int) $item->quantity,
                    'unit_cost' => $cost,
                    'base_quantity' => $item->quantity,
                ]);

                $movement = Movement::create([
                    'product_id' => $product->id,
                    'type' => 'entry',
                    'quantity' => (float) $item->quantity,
                    'unit_cost' => $cost,
                    'description' => 'Recepción cotización ' . $quotation->code,
                    'user_id' => Auth::id(),
                    'branch_id' => $quotation->branch_id,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                // Sumar al inventario de la sucursal (BranchInventory)
                \App\Models\BranchInventory::firstOrCreate([
                    'branch_id' => $quotation->branch_id,
                    'product_id' => $product->id,
                ])->increment('allocated_quantity', (float) $item->quantity);

                if ($branch?->company_id) {
                    $inventoryService->processCompanyPurchaseEntry($branch->company_id, $product, (float) $item->quantity, $cost, $movement);
                } else {
                    $inventoryService->processPurchaseEntry($product, (float) $item->quantity, $cost, $movement);
                }
            }

            $quotation->update([
                'status' => 'received',
                'received_by' => Auth::id(),
                'received_at' => now(),
                'purchase_id' => $purchase->id,
            ]);

            DB::commit();
            $this->dispatch('show-toast', type: 'success', message: "Cotización recibida. Se generó la compra y entró el stock.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $allowed = $user->allowedBranchIds();

        // El flujo de aprobación nunca incluye borradores (van en "Mis borradores").
        $quotations = Quotation::with('supplier', 'branch', 'creator', 'approver', 'payer', 'items')
            ->where('status', '!=', 'draft')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when(! $user->can('access_all_branches') && $allowed, fn ($q) => $q->whereIn('branch_id', $allowed))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // "Mis borradores": solo los del usuario actual (quien los creó).
        $drafts = Quotation::with('supplier', 'items')
            ->where('status', 'draft')
            ->where('created_by', $user->id)
            ->when(! $user->can('access_all_branches') && $allowed, fn ($q) => $q->whereIn('branch_id', $allowed))
            ->orderBy('updated_at', 'desc')
            ->take(30)
            ->get();

        return view('livewire.bodega.quotation-index', compact('quotations', 'drafts'))
            ->layout('components.layouts.app');
    }
}
