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

    public function approve($id)
    {
        $quotation = Quotation::findOrFail($id);

        if ($quotation->status !== 'pending') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden aprobar cotizaciones pendientes.');
            return;
        }
        if (auth()->user()->cannot('approve quotations')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tenés permiso para aprobar cotizaciones.');
            return;
        }

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

        if ($quotation->status !== 'approved') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden pagar cotizaciones aprobadas.');
            return;
        }
        if (auth()->user()->cannot('pay quotations')) {
            $this->dispatch('show-toast', type: 'error', message: 'No tenés permiso para pagar cotizaciones.');
            return;
        }

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

        if ($quotation->status !== 'paid') {
            $this->dispatch('show-toast', type: 'error', message: 'Solo se pueden recibir cotizaciones pagadas.');
            return;
        }

        if ($quotation->purchase_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Esta cotización ya fue recibida.');
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = 'COT-' . $quotation->code;
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
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_cost' => (float) $item->unit_cost,
                    'base_quantity' => $item->quantity,
                ]);

                $product = $item->product;
                $cost = (float) $item->unit_cost;

                $movement = Movement::create([
                    'product_id' => $item->product_id,
                    'type' => 'entry',
                    'quantity' => (float) $item->quantity,
                    'unit_cost' => $cost,
                    'description' => 'Recepción cotización ' . $quotation->code,
                    'user_id' => Auth::id(),
                    'branch_id' => $quotation->branch_id,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                ]);

                $branch = $quotation->branch;
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
        $quotations = Quotation::with('supplier', 'branch', 'creator', 'approver', 'payer', 'items')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.bodega.quotation-index', compact('quotations'))
            ->layout('components.layouts.app');
    }
}
