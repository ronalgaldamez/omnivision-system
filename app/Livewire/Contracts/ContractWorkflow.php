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
use Illuminate\Support\Str;

class ContractWorkflow extends Component
{
    use WithFileUploads;

    public $ticket_id;
    public $step = 1;
    protected $queryString = ['step'];
    public $contract_id = null;

    // ─── Step 1: Datos del Cliente ───
    public $client_id;
    public $client_name;
    public $client_document_type;
    public $client_document_number;
    public $client_phone;
    public $client_email;
    public $client_address;
    public $client_branch_name;
    public $installation_address;
    public $latitude;
    public $longitude;
    public $gps_link = null;

    // ─── Datos del Ticket ───
    public $ticket_description;
    public $ticket_priority;
    public $ticket_origin;

    // ─── Notas del Cliente ───
    public $client_notes;

    // ─── Planes de Referencia ───
    public $quickReferencePlans = [];
    public $isPotentialClient = false;
    public $showQuickReferencePlans = false;

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
    public $receipt = null;
    public $document_notes = '';
    public $uploadedDocuments = [];
    public $docs_link = null;

    // ─── Documentos subidos por el cliente vía enlace público ───
    public $clientUploadedDocs = [];

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
                'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
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
            $ticket = Ticket::with('client.branch', 'client.zone.parent.parent', 'zone.parent.parent')->find($ticket_id);
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

            // ─── Sucursal: resolver desde cliente, zona del cliente o zona del ticket ───
            // Busca primero branch directo del cliente, luego sube por el árbol de zonas
            if ($client->branch) {
                $this->client_branch_name = $client->branch->name;
            } else {
                $branch = $this->resolveBranchFromZone($client->zone)
                      ?? $this->resolveBranchFromZone($ticket->zone);
                $this->client_branch_name = $branch?->name ?? '—';
            }

            // ─── Datos del Ticket ───
            $this->ticket_description = $ticket->description;
            $this->ticket_priority = $ticket->priority;
            $this->ticket_origin = $ticket->origin;

            // ─── Notas del Cliente ───
            $this->client_notes = $client->notes;

            // ─── Detectar tipo de servicio y cargar planes de referencia ───
            $serviceType = \App\Models\ServiceType::where('name', $ticket->service_type)->first();
            $this->isPotentialClient = $serviceType && $serviceType->requires_potential;
            $this->showQuickReferencePlans = $serviceType && ($serviceType->requires_potential || $serviceType->requires_contract);
            if ($this->showQuickReferencePlans) {
                $this->quickReferencePlans = Plan::where('is_active', true)->get()->toArray();
            }

            // ─── Si el ticket ya tiene plan, cargarlo ───
            if ($ticket->plan_id) {
                $this->plan_id = $ticket->plan_id;
                if ($this->zone_id) {
                    $this->updateEffectivePrice();
                }
            }
        }

        $this->contract_terms = $this->getDefaultTerms();

        $this->loadClientUploadedDocs();
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

    // ─── Planes de Referencia (Cliente Potencial) ───

    public function addPlanReference($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) return;

        $price = $this->zone_id
            ? optional(Zone::find($this->zone_id))->getEffectivePriceForPlan($plan)
            : $plan->base_price;

        // Asignar el plan directamente
        $this->plan_id = $plan->id;
        $this->price = $price ?? $plan->base_price;
        $this->effective_price = $this->price;

        $this->dispatch('show-toast', type: 'success', message: "Plan «{$plan->name}» seleccionado.");
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
            'receipt' => 'receipt',
        ];

        $type = $typeMap[$field] ?? 'other';
        $folder = 'clients/' . $this->client_id . '/documents';
        $path = $file->store($folder, 's3');

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
            Storage::disk('s3')->delete($this->uploadedDocuments[$type]['path']);
            unset($this->uploadedDocuments[$type]);
        }

        // Resetear el file input
        $map = [
            'dui_front' => 'dui_front',
            'dui_back' => 'dui_back',
            'receipt' => 'receipt',
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
        $optional = ['receipt'];

        // Combinar documentos subidos por el agente y por el cliente
        $uploaded = array_keys($this->uploadedDocuments);
        $clientTypes = array_column($this->clientUploadedDocs, 'type');
        $allUploaded = array_unique(array_merge($uploaded, $clientTypes));

        $requiredCompleted = empty(array_diff($required, $allUploaded));
        $totalRequired = count($required);
        $completedRequired = count(array_intersect($required, $allUploaded));

        return [
            'required_completed' => $requiredCompleted,
            'completed_required' => $completedRequired,
            'total_required' => $totalRequired,
            'completed_optional' => count(array_intersect($optional, $allUploaded)),
            'total' => count($allUploaded),
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

        // Guardar documentos subidos por el agente
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

        // Guardar documentos subidos por el cliente vía enlace público
        foreach ($this->clientUploadedDocs as $doc) {
            ContractDocument::create([
                'contract_id' => $contract->id,
                'type' => $doc['type'],
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

    // ─── GPS / Coordenadas ───

    public function generateGpsLink()
    {
        if (!$this->client_id) {
            $this->dispatch('show-toast', type: 'error', message: 'No hay cliente seleccionado.');
            return;
        }

        $client = Client::find($this->client_id);
        if (!$client) {
            $this->dispatch('show-toast', type: 'error', message: 'Cliente no encontrado.');
            return;
        }

        // Si el token actual es válido (no expirado), reusarlo
        $now = now();
        if ($client->gps_token && $client->gps_token_expires_at && $client->gps_token_expires_at->greaterThan($now)) {
            $this->gps_link = route('public.contract.coordinates', ['token' => $client->gps_token]);
            $this->dispatch('show-toast', type: 'success', message: 'Enlace vigente reutilizado.');
            return;
        }

        // Generar nuevo token con caducidad de 24 horas
        $client->update([
            'gps_token' => (string) Str::uuid(),
            'gps_token_expires_at' => $now->copy()->addHours(24),
        ]);

        $this->gps_link = route('public.contract.coordinates', ['token' => $client->gps_token]);

        $this->dispatch('show-toast', type: 'success', message: 'Enlace generado. Enviáselo al cliente por WhatsApp.');
    }

    public function getGpsWhatsAppUrl(): ?string
    {
        $client = Client::find($this->client_id);
        if (!$client || !$client->phone) return null;

        // Asegurar que el enlace GPS esté generado
        if (!$this->gps_link) {
            $this->generateGpsLink();
        }
        if (!$this->gps_link) return null;

        // Limpiar el teléfono: dejar solo dígitos
        $phone = preg_replace('/\D/', '', $client->phone);
        // Si empieza con 0, quitarlo; si no tiene código de país, asumir 503
        if (strlen($phone) === 8) {
            $phone = '503' . $phone;
        } elseif (strlen($phone) === 9 && $phone[0] === '0') {
            $phone = '503' . substr($phone, 1);
        }

        $message = "Hola, soy de Omnivisión. Para continuar con tu instalación necesitamos tus coordenadas. Hacé clic en este enlace y permití el acceso a tu ubicación:\n\n";
        $message .= $this->gps_link . "\n\n";
        $message .= "⚠️ Si no estás en casa en este momento, compartí este enlace con un familiar o la persona que esté en la dirección de instalación para que capture las coordenadas desde ahí. ¡Gracias!";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function sendGpsViaWhatsApp()
    {
        $url = $this->getGpsWhatsAppUrl();
        if (!$url) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente no tiene un número de teléfono registrado.');
            return;
        }

        $this->dispatch('open-whatsapp', url: $url);
    }

    public function refreshCoordinates()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        if ($client && $client->latitude && $client->longitude) {
            $this->latitude = $client->latitude;
            $this->longitude = $client->longitude;
            $this->dispatch('show-toast', type: 'success', message: 'Coordenadas actualizadas desde el cliente.');
        }
    }

    // ─── Documentos: Enlace público de subida ───

    public function loadClientUploadedDocs()
    {
        if (!$this->client_id) return;
        $client = Client::find($this->client_id);
        if ($client) {
            $this->clientUploadedDocs = $client->uploaded_docs ?? [];
        }
    }

    public function generateDocsLink()
    {
        if (!$this->client_id) {
            $this->dispatch('show-toast', type: 'error', message: 'No hay cliente seleccionado.');
            return;
        }

        $client = Client::find($this->client_id);
        if (!$client) {
            $this->dispatch('show-toast', type: 'error', message: 'Cliente no encontrado.');
            return;
        }

        $now = now();
        if ($client->docs_token && $client->docs_token_expires_at && $client->docs_token_expires_at->greaterThan($now)) {
            $this->docs_link = route('public.contract.documents', ['token' => $client->docs_token]);
            $this->dispatch('show-toast', type: 'success', message: 'Enlace vigente reutilizado.');
            return;
        }

        $client->update([
            'docs_token' => (string) Str::uuid(),
            'docs_token_expires_at' => $now->copy()->addHours(24),
        ]);

        $this->docs_link = route('public.contract.documents', ['token' => $client->docs_token]);

        $this->dispatch('show-toast', type: 'success', message: 'Enlace generado. Enviáselo al cliente por WhatsApp.');
    }

    public function getDocsWhatsAppUrl(): ?string
    {
        $client = Client::find($this->client_id);
        if (!$client || !$client->phone) return null;

        if (!$this->docs_link) {
            $this->generateDocsLink();
        }
        if (!$this->docs_link) return null;

        $phone = preg_replace('/\D/', '', $client->phone);
        if (strlen($phone) === 8) {
            $phone = '503' . $phone;
        } elseif (strlen($phone) === 9 && $phone[0] === '0') {
            $phone = '503' . substr($phone, 1);
        }

        $message = "Hola, soy de Omnivisión. Para continuar con tu contrato necesitamos que subas tus documentos. Hacé clic en este enlace y adjuntá DUI (frente y reverso) y recibo de luz:\n\n";
        $message .= $this->docs_link . "\n\n";
        $message .= "⚠️ El enlace expira en 24 horas.";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function sendDocsViaWhatsApp()
    {
        $url = $this->getDocsWhatsAppUrl();
        if (!$url) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente no tiene un número de teléfono registrado.');
            return;
        }

        $this->dispatch('open-whatsapp', url: $url);
    }

    public function refreshUploadedDocs()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        if ($client) {
            $this->clientUploadedDocs = $client->uploaded_docs ?? [];
            $this->dispatch('show-toast', type: 'success', message: 'Documentos actualizados desde el cliente.');
        }
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

    /**
     * Sube por el árbol de padres de una zona hasta encontrar una que tenga branch_id.
     */
    private function resolveBranchFromZone($zone): ?\App\Models\Branch
    {
        if (!$zone) return null;

        $current = $zone;
        while ($current) {
            if ($current->branch) {
                return $current->branch;
            }
            $current = $current->parent;
        }

        return null;
    }

    public function render()
    {
        return view('livewire.contracts.contract-workflow')
            ->layout('components.layouts.app');
    }
}
