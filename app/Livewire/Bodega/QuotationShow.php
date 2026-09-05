<?php

namespace App\Livewire\Bodega;

use App\Models\Quotation;
use Livewire\Component;

class QuotationShow extends Component
{
    public $quotation;

    public $steps = [];

    public function mount($id)
    {
        $this->quotation = Quotation::with([
            'supplier', 'branch', 'creator', 'approver', 'payer', 'receiver',
            'purchase', 'items.product', 'items.pendingCategory',
        ])->findOrFail($id);

        // Misma visibilidad que el index: el usuario solo ve cotizaciones de sus sucursales.
        $user = auth()->user();
        if (! $user->can('access_all_branches')) {
            $allowed = $user->allowedBranchIds();
            if ($allowed === [] || ! in_array($this->quotation->branch_id, $allowed, true)) {
                abort(403);
            }
        }

        // Los borradores solo son visibles para su creador.
        if ($this->quotation->status === 'draft' && (int) $this->quotation->created_by !== (int) $user->id) {
            abort(403);
        }

        $this->buildSteps();
    }

    private function buildSteps(): void
    {
        $status = $this->quotation->status;
        $is = fn (string $target) => $status === $target;
        $in = fn (array $targets) => in_array($status, $targets, true);

        // Flujo borrador: solo muestra la etapa actual.
        if ($status === 'draft') {
            $this->steps = [
                [
                    'key' => 'draft',
                    'icon' => 'edit_note',
                    'name' => 'Borrador',
                    'description' => 'Cotización en espera: todavía no se envió a aprobación',
                    'timestamp' => $this->quotation->updated_at,
                    'user' => $this->quotation->creator?->name,
                    'completed' => true,
                    'active' => true,
                ],
            ];

            return;
        }

        // Flujo rechazado: timeline corta (creación + rechazo con motivo).
        if ($status === 'rejected') {
            $this->steps = [
                [
                    'key' => 'pending',
                    'icon' => 'request_quote',
                    'name' => 'Pendiente',
                    'description' => 'Cotización creada por el bodeguero',
                    'timestamp' => $this->quotation->created_at,
                    'user' => $this->quotation->creator?->name,
                    'completed' => true,
                    'active' => false,
                ],
                [
                    'key' => 'rejected',
                    'icon' => 'block',
                    'name' => 'Rechazada',
                    'description' => $this->quotation->rejection_reason ?: 'Rechazada por el gerente administrativo',
                    'timestamp' => $this->quotation->updated_at,
                    'user' => null,
                    'completed' => true,
                    'active' => true,
                    'tone' => 'red',
                ],
            ];

            return;
        }

        $this->steps = [
            [
                'key' => 'pending',
                'icon' => 'request_quote',
                'name' => 'Pendiente',
                'description' => 'Cotización creada por el bodeguero, esperando aprobación',
                'timestamp' => $this->quotation->created_at,
                'user' => $this->quotation->creator?->name,
                'completed' => true,
                'active' => $is('pending'),
            ],
            [
                'key' => 'approved',
                'icon' => 'check',
                'name' => 'Aprobada',
                'description' => 'El gerente administrativo aprobó la compra',
                'timestamp' => $this->quotation->approved_at,
                'user' => $this->quotation->approver?->name,
                'completed' => $in(['approved', 'paid', 'received']),
                'active' => $is('approved'),
            ],
            [
                'key' => 'paid',
                'icon' => 'payments',
                'name' => 'Pagada',
                'description' => 'El subgerente administrativo confirmó el pago',
                'timestamp' => $this->quotation->paid_at,
                'user' => $this->quotation->payer?->name,
                'completed' => $in(['paid', 'received']),
                'active' => $is('paid'),
            ],
            [
                'key' => 'received',
                'icon' => 'inventory_2',
                'name' => 'Recibida',
                'description' => 'Se generó la compra y entró el stock',
                'timestamp' => $this->quotation->received_at,
                'user' => $this->quotation->receiver?->name,
                'completed' => $is('received'),
                'active' => $is('received'),
            ],
        ];
    }

    public function render()
    {
        $statusMap = [
            'draft' => ['Borrador', 'bg-gray-200 text-gray-600'],
            'pending' => ['Pendiente', 'bg-gray-100 text-gray-700'],
            'approved' => ['Aprobada', 'bg-blue-50 text-blue-700'],
            'paid' => ['Pagada', 'bg-purple-50 text-purple-700'],
            'received' => ['Recibida', 'bg-green-50 text-green-700'],
            'rejected' => ['Rechazada', 'bg-red-50 text-red-700'],
        ];

        $statusInfo = $statusMap[$this->quotation->status] ?? [$this->quotation->status, 'bg-gray-100 text-gray-700'];

        return view('livewire.bodega.quotation-show', compact('statusInfo'))
            ->layout('components.layouts.app');
    }
}
