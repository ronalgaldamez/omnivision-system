<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\Zone;
use App\Services\WorkOrderService;
use App\Services\ContractPdfService;
use App\Services\ContractSignatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractWorkflow extends Component
{
    use WithFileUploads;

    public $ticket_id;
    public $step = 1;
    public $contract_id = null;

    // ─── Step 1: Datos del Cliente ───
    public $client_id;
    public $client_name;
    public $client_document_type;
    public $client_document_number;
    public $client_phone;
    public $client_email;
    public $client_address;
    public $installation_address;
    public $latitude;
    public $longitude;

    // ─── Step 2: Plan y Precio ───
    public $plan_id = '';
    public $zone_id = '';
    public $service_type;
    public $price;
    public $effective_price = 0;
    public $availablePlans = [];
    public $availableZones = [];

    // ─── Step 3: Documentos ───
    public $dui_front = null;
    public $dui_back = null;
    public $selfie = null;
    public $receipt = null;
    public $proof_of_address = null;
    public $document_notes = '';
    public $uploadedDocuments = [];

    // ─── Step 4: Firma Digital ───
    public $client_signature_data = null;
    public $sales_rep_signature_data = null;
    public $signature_link = null;
    public $showSignatureCanvas = false;
    public $showClientSignature = false;
    public $showSalesRepSignature = false;

    // ─── Step 5: Preview PDF ───
    public $contract_terms;
    public $showPdfPreview = false;
    public $pdfPreviewUrl = null;
    public $contractDigitalCode = null;

    protected $listeners = [
        'signatureSaved',
        'documentUploaded',
    ];

    protected function rules()
    {
        return match ($this->step) {
            1 => [
                'installation_address' => 'required|string|max:500',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ],
            2 => [
                'plan_id' => 'required|exists:plans,id',
                'price' => 'required|numeric|min:0',
            ],
            3 => [
                'dui_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'dui_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'selfie' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
                'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'proof_of_address' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ],
            4 => [
                'client_signature_data' => 'required_without:signature_link',
            ],
            default => [],
        };
    }

    protected $messages = [
        'installation_address.required' => 'La dirección de instalación es obligatoria.',
        'plan_id.required' => 'Debe seleccionar un plan.',
        'price.required' => 'El precio es obligatorio.',
        'price.numeric' => 'El precio debe ser un valor numérico.',
        'client_signature_data.required_without' => 'Debe capturar la firma del cliente o enviar un enlace.',
    ];

    public function mount(?int $ticket_id = null)
    {
        if (Auth::user()->cannot('access_contracts_inbox')) {
            abort(403);
        }

        $this->availablePlans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
        $this->availableZones = Zone::orderBy('name')->get(['id', 'name'])->toArray();

        if ($ticket_id) {
            $ticket = Ticket::with('client.zone')->find($ticket_id);
            if (!$ticket || !$ticket->requires_contract) {
                abort(404);
            }

            $this->ticket_id = $ticket->id;
            $client = $ticket->client;

            // Precargar datos del cliente
            $this->client_id = $client->id;
            $this->client_name = $client->name;
            $this->client_document_type = $client->document_type;
            $this->client_document_number = $client->document_number;
            $this->client_phone = $client->phone;
            $this->client_email = $client->email;
            $this->client_address = $client->address;
            $this->installation_address = $client->installation_address ?? $client->address ?? '';
            $this->latitude = $client->latitude ?? '';
            $this->longitude = $client->longitude ?? '';
            $this->service_type = $ticket->service_type;
            $this->zone_id = $ticket->zone_id ?? $client->zone_id ?? '';

            // Si tiene zona, cargar precio efectivo
            if ($this->zone_id && $ticket->plan_id) {
                $this->plan_id = $ticket->plan_id;
                $this->updateEffectivePrice();
            }
        }

        $this->contract_terms = $this->getDefaultTerms();
    }

    // ─── Navegación del Wizard ───

    public function goToStep($step)
    {
        if ($step < $this->step) {
            $this->step = $step;
            return;
        }

        // Si ya hay contrato creado, permitir ir a cualquier paso
        if ($this->contract_id) {
            $this->step = $step;
            return;
        }

        $this->step = $step;
    }

    public function nextStep()
    {
        $this->validate();

        if ($this->step === 1) {
            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->step = 3;
        } elseif ($this->step === 3) {
            $this->step = 4;
        } elseif ($this->step === 4) {
            // Cuando se completa la firma, ir a preview
            $this->step = 5;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // ─── Step 2: Plan ───

    public function updatedPlanId($value)
    {
        $this->updateEffectivePrice();
    }

    public function updatedZoneId($value)
    {
        $this->updateEffectivePrice();
    }

    public function updateEffectivePrice()
    {
        if (!$this->plan_id) {
            $this->effective_price = 0;
            $this->price = 0;
            return;
        }

        $plan = Plan::find($this->plan_id);
        if (!$plan) return;

        if ($this->zone_id) {
            $zone = Zone::find($this->zone_id);
            if ($zone) {
                $this->effective_price = $zone->getEffectivePriceForPlan($plan);
            } else {
                $this->effective_price = (float) $plan->base_price;
            }
        } else {
            $this->effective_price = (float) $plan->base_price;
        }

        $this->price = $this->effective_price;
    }

    public function getPlanPriceDetailProperty()
    {
        if (!$this->plan_id) return null;

        $plan = Plan::find($this->plan_id);
        if (!$plan) return null;

        return [
            'base_price' => (float) $plan->base_price,
            'effective_price' => $this->effective_price,
            'has_override' => $this->effective_price != (float) $plan->base_price,
        ];
    }

    // ─── Step 3: Documentos ───

    public function uploadDocument($field)
    {
        $this->validateOnly($field);

        $file = $this->$field;
        if (!$file) return;

        $typeMap = [
            'dui_front' => 'dui_front',
            'dui_back' => 'dui_back',
            'selfie' => 'selfie',
            'receipt' => 'receipt',
            'proof_of_address' => 'proof_of_address',
        ];

        $type = $typeMap[$field] ?? 'other';
        $path = $file->store('contract-documents', 'public');

        $this->uploadedDocuments[$type] = [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'temp' => true,
        ];

        $this->dispatch('show-toast', type: 'success', message: 'Documento subido correctamente.');
    }

    public function removeDocument($type)
    {
        if (isset($this->uploadedDocuments[$type])) {
            Storage::disk('public')->delete($this->uploadedDocuments[$type]['path']);
            unset($this->uploadedDocuments[$type]);
        }

        // Resetear el file input
        $map = [
            'dui_front' => 'dui_front',
            'dui_back' => 'dui_back',
            'selfie' => 'selfie',
            'receipt' => 'receipt',
            'proof_of_address' => 'proof_of_address',
        ];

        $field = array_search($type, $map);
        if ($field) {
            $this->$field = null;
        }

        $this->dispatch('show-toast', type: 'info', message: 'Documento eliminado.');
    }

    public function getDocumentsProgressProperty(): array
    {
        $required = ['dui_front', 'dui_back'];
        $optional = ['selfie', 'receipt', 'proof_of_address'];

        $uploaded = array_keys($this->uploadedDocuments);

        $requiredCompleted = empty(array_diff($required, $uploaded));
        $totalRequired = count($required);
        $completedRequired = count(array_intersect($required, $uploaded));

        return [
            'required_completed' => $requiredCompleted,
            'completed_required' => $completedRequired,
            'total_required' => $totalRequired,
            'completed_optional' => count(array_intersect($optional, $uploaded)),
            'total' => count($uploaded),
        ];
    }

    // ─── Step 4: Firma ───

    public function saveClientSignature($signatureData)
    {
        $this->client_signature_data = $signatureData;
        $this->showClientSignature = true;
        $this->dispatch('show-toast', type: 'success', message: 'Firma del cliente capturada.');
    }

    public function saveSalesRepSignature($signatureData)
    {
        $this->sales_rep_signature_data = $signatureData;
        $this->showSalesRepSignature = true;
        $this->dispatch('show-toast', type: 'success', message: 'Tu firma ha sido capturada.');
    }

    public function generateSignatureLink()
    {
        if (!$this->contract_id) {
            $this->dispatch('show-toast', type: 'error', message: 'Primero debe crear el contrato.');
            return;
        }

        $contract = Contract::find($this->contract_id);
        $service = app(ContractSignatureService::class);
        $this->signature_link = $service->generateSignatureLink($contract);

        $this->dispatch('show-toast', type: 'success', message: 'Enlace de firma generado. Compártelo con el cliente.');
    }

    public function signatureSaved()
    {
        $this->dispatch('show-toast', type: 'success', message: 'Firma registrada correctamente.');
        $this->showClientSignature = true;
    }

    // ─── Step 5: Finalizar ───

    public function createContract()
    {
        $this->validate();

        $contract = Contract::create([
            'client_id' => $this->client_id,
            'ticket_id' => $this->ticket_id,
            'plan_id' => $this->plan_id ?: null,
            'zone_id' => $this->zone_id ?: null,
            'service_type' => $this->service_type,
            'price' => $this->price,
            'installation_address' => $this->installation_address,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'contract_terms' => $this->contract_terms,
            'contract_date' => now()->format('Y-m-d'),
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        $this->contract_id = $contract->id;
        $this->contractDigitalCode = $contract->contract_digital_code;

        // Guardar documentos subidos
        foreach ($this->uploadedDocuments as $type => $doc) {
            ContractDocument::create([
                'contract_id' => $contract->id,
                'type' => $type,
                'file_path' => $doc['path'],
                'original_name' => $doc['original_name'],
                'mime_type' => $doc['mime_type'],
                'file_size' => $doc['file_size'],
            ]);
        }

        // Guardar firmas
        $sigService = app(ContractSignatureService::class);

        if ($this->client_signature_data) {
            $sigService->saveSignature($contract, 'client', $this->client_signature_data);
        }

        if ($this->sales_rep_signature_data) {
            $sigService->saveSignature($contract, 'sales_rep', $this->sales_rep_signature_data);
        }

        // Generar PDF
        $pdfService = app(ContractPdfService::class);
        $pdfService->generate($contract);

        // Crear OT si viene de ticket
        if ($this->ticket_id) {
            $ticket = Ticket::with('client')->find($this->ticket_id);
            if ($ticket) {
                app(WorkOrderService::class)->createFromTicket($ticket);

                $ticket->update([
                    'contracts_ended_at' => now(),
                    'status' => 'in_progress',
                ]);
                app(\App\Services\SlaService::class)->evaluateSla($ticket);
            }
        }

        $this->step = 5;
        $this->dispatch('show-toast', type: 'success', message: 'Contrato #' . $contract->contract_digital_code . ' creado correctamente.');
    }

    public function downloadPdf()
    {
        if (!$this->contract_id) return;

        $contract = Contract::find($this->contract_id);
        $pdfService = app(ContractPdfService::class);

        if ($pdfService->hasPdf($contract)) {
            return Storage::disk('public')->download($contract->signed_pdf_path);
        }

        $pdfService->generate($contract);
        return Storage::disk('public')->download($contract->signed_pdf_path);
    }

    public function finalize()
    {
        if ($this->ticket_id) {
            return redirect()->route('contracts.inbox', ['ticket_id' => $this->ticket_id]);
        }

        return redirect()->route('contracts.index');
    }

    // ─── Utilidades ───

    private function getDefaultTerms(): string
    {
        return '<p><strong>Primero:</strong> El proveedor se compromete a instalar y proporcionar el servicio contratado en la dirección indicada por el cliente.</p>
        <p><strong>Segundo:</strong> El cliente se obliga al pago puntual de la tarifa acordada por el servicio, la cual podrá ser ajustada previa notificación con 30 días de anticipación.</p>
        <p><strong>Tercero:</strong> El período mínimo de contratación es de 12 meses. En caso de cancelación anticipada, el cliente deberá pagar una penalidad equivalente al 25% del saldo restante.</p>
        <p><strong>Cuarto:</strong> El proveedor garantiza el servicio con una disponibilidad mínima del 99.5% mensual, excluyendo mantenimientos programados y casos de fuerza mayor.</p>
        <p><strong>Quinto:</strong> El cliente autoriza el uso de sus datos personales únicamente para fines de facturación y soporte técnico, conforme a la Ley de Protección de Datos.</p>';
    }

    public function render()
    {
        return view('livewire.contracts.contract-workflow')
            ->layout('components.layouts.app');
    }
}
