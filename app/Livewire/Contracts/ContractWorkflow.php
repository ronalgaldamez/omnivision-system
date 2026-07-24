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
    public $waitingForCoordinates = false;

    // ─── Datos legales del cliente (contrato) ───
    public $client_nit;
    public $client_nrc;
    public $dui_expedition_date;
    public $dui_expedition_place;
    public $client_nationality;
    public $client_marital_status;
    public $client_spouse_name;
    public $client_occupation;
    public $client_workplace;
    public $client_position;
    public $client_monthly_income;
    public $client_boss_name;
    public $client_work_phone;
    public $client_work_address;
    public $client_billing_address;

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
    public $fachada = null;
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
                'client_nit' => 'nullable|string|max:20',
                'client_billing_address' => 'required|string|max:500',
            ],
            2 => [
                'plan_id' => 'required|exists:plans,id',
                'price' => 'required|numeric|min:0',
            ],
            3 => [
                'dui_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'dui_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'fachada' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
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

            // ─── Datos legales del contrato ───
            $this->client_nit = $client->nit ?? '';
            $this->client_nrc = $client->nrc ?? '';
            $this->dui_expedition_date = $client->dui_expedition_date ?? '';
            $this->dui_expedition_place = $client->dui_expedition_place ?? '';
            $this->client_nationality = $client->nationality ?? '';
            $this->client_marital_status = $client->marital_status ?? '';
            $this->client_spouse_name = $client->spouse_name ?? '';
            $this->client_occupation = $client->occupation ?? '';
            $this->client_workplace = $client->workplace ?? '';
            $this->client_position = $client->position ?? '';
            $this->client_monthly_income = $client->monthly_income ?? '';
            $this->client_boss_name = $client->boss_name ?? '';
            $this->client_work_phone = $client->work_phone ?? '';
            $this->client_work_address = $client->work_address ?? '';
            $this->client_billing_address = $client->billing_address ?? $client->address ?? '';

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
            $progress = $this->documentsProgress;
            if (!$progress['required_completed']) {
                $this->dispatch('show-toast', type: 'error', message: 'Todos los documentos son obligatorios (DUI frente, DUI reverso, Recibo de luz y Foto de fachada).');
                return;
            }
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
            'fachada' => 'fachada',
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
            'fachada' => 'fachada',
        ];

        $field = array_search($type, $map);
        if ($field) {
            $this->$field = null;
        }

        $this->dispatch('show-toast', type: 'info', message: 'Documento eliminado.');
    }

    public function getDocumentsProgressProperty(): array
    {
        $required = ['dui_front', 'dui_back', 'receipt', 'fachada'];
        $optional = [];

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

        // Guardar datos legales en el cliente
        Client::where('id', $this->client_id)->update([
            'nit' => $this->client_nit,
            'nrc' => $this->client_nrc,
            'dui_expedition_date' => $this->dui_expedition_date ?: null,
            'dui_expedition_place' => $this->dui_expedition_place,
            'nationality' => $this->client_nationality,
            'marital_status' => $this->client_marital_status,
            'spouse_name' => $this->client_spouse_name,
            'occupation' => $this->client_occupation,
            'workplace' => $this->client_workplace,
            'position' => $this->client_position,
            'monthly_income' => $this->client_monthly_income ?: null,
            'boss_name' => $this->client_boss_name,
            'work_phone' => $this->client_work_phone,
            'work_address' => $this->client_work_address,
            'billing_address' => $this->client_billing_address,
            'installation_address' => $this->installation_address,
        ]);

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
        $this->waitingForCoordinates = true;

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
            $this->waitingForCoordinates = false;
            $this->dispatch('show-toast', type: 'success', message: 'Coordenadas actualizadas desde el cliente.');
        }
    }

    // ─── Rechazar documentos subidos por el cliente ───

    public function rejectClientDoc($type)
    {
        $client = Client::find($this->client_id);
        if (!$client) return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $i => $d) {
            if ($d['type'] === $type) {
                Storage::disk('s3')->delete($d['path']);
                unset($docs[$i]);
                break;
            }
        }
        $client->update(['uploaded_docs' => array_values($docs)]);
        $this->clientUploadedDocs = $client->fresh()->uploaded_docs ?? [];

        $labels = ['dui_front' => 'DUI (Frente)', 'dui_back' => 'DUI (Reverso)', 'receipt' => 'Recibo de luz', 'fachada' => 'Foto de Fachada'];
        $this->dispatch('show-toast', type: 'info', message: $labels[$type] . ' rechazado.');
    }

    public function rejectAllClientDocs()
    {
        $client = Client::find($this->client_id);
        if (!$client) return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $d) {
            Storage::disk('s3')->delete($d['path']);
        }

        $client->update([
            'uploaded_docs' => [],
            'docs_token' => null,
            'docs_token_expires_at' => null,
        ]);
        $this->clientUploadedDocs = [];
        $this->docs_link = null;

        $this->dispatch('show-toast', type: 'info', message: 'Todos los documentos fueron rechazados. Generá un nuevo enlace.');
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

        $message = "Hola, soy de Omnivisión. Para continuar con tu contrato necesitamos que subas tus documentos. Hacé clic en este enlace y adjuntá DUI (frente y reverso), recibo de luz y una foto de la fachada de tu casa:\n\n";
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

    public function getDocPreviewUrl($path): ?string
    {
        if (!$path) return null;
        try {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(10));
        } catch (\Exception $e) {
            return null;
        }
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


    private function getDefaultTerms(): string
    {
        return '
        <p><strong>SECCION PRIMERA: DATOS GENERALES DEL CLIENTE.</strong></p>
        <p>Nombre Completo: ' . e($this->client_name ?? '') . '</p>
        <p>DUI: ' . e($this->client_document_number ?? '') . '</p>
        <p>NIT: ' . e($this->client_nit ?? '') . '</p>
        <p>Dirección de instalación: ' . e($this->installation_address ?? '') . '</p>
        <p>Dirección de cobro: ' . e($this->client_billing_address ?? '') . '</p>

        <p><strong>SECCION SEGUNDA: ESPECIFICACIONES DE LOS SERVICIOS PRESTADOS AL CLIENTE.</strong></p>

        <p><strong>1.</strong> CLIENTE: Declaro que recibiré de parte de OMNIVISION-OMNICOM el servicio de telecomunicaciones hasta la finalización del plazo acordado; y estoy consciente que el contrato de servicio entra en vigencia a partir de la fecha de suscripción.</p>

        <p><strong>2. TARIFAS Y PRECIOS:</strong> Las tarifas y precios estarán consignadas en este contrato. Por el servicio que reciba me obligo a pagar a OMNIVISION-OMNICOM: I) Tarifa y Precio por el valor del paquete contratado. II) Precio por activación, instalación, desactivación, desinstalación, traslado de servicio, recargos por facturas vencidas y otros semejantes previamente informados. III) Precio por venta o arrendamiento de equipo.</p>

        <p><strong>3. FACTURACION:</strong> Me comprometo a pagar los servicios antes indicados en dólares de los Estados Unidos de América, en concepto de servicios contratados, los cuales serán facturados por períodos mensuales de acuerdo al sistema de facturación utilizado por OMNIVISION-OMNICOM. Así mismo tengo el conocimiento que si al día del inicio del servicio faltare menos de un mes para la emisión de la factura correspondiente, los cargos básicos se me facturarán proporcional. También deberé pagar dicha factura o crédito fiscal como máximo en la fecha última de pago que se me ha indicado por cualquier medio verificable que disponga la empresa. La falta de recibir el documento de cobro correspondiente, no me exime de la responsabilidad del pago oportuno.</p>

        <p><strong>4. VIGENCIA Y PLAZO:</strong> El plazo obligatorio de vigencia aplicable al servicio de cable tv e Internet, prestado por OMNIVISION-OMNICOM se estipula en este contrato de servicio que suscribo y entrará en vigencia a partir de la fecha de mi suscripción, luego de finalizado el plazo obligatorio.</p>

        <p><strong>5. TERMINACION CONTRACTUAL Y CONDICIONES DE RETIRO ANTICIPADO:</strong> En caso de dar por terminado el contrato de servicio tv e Internet, dentro del plazo obligatorio establecido en el presente contrato, debo de notificar por escrito a las oficinas administrativas con diez días hábiles de anticipación al retiro efectivo del servicio, deberé pagar todos y cada uno de los montos adecuados al momento de la terminación (Valor del número de meses restante para la finalización del contrato), y penalidades por terminación anticipada de manera particular.</p>

        <p><strong>6. EL SERVICIO CONTRATADO PODRA SUSPENDERSE EN LOS CASOS SIGUIENTES:</strong> OMNIVISION-OMNICOM, podrá suspender la prestación de servicio de cable tv e Internet por incumplimiento de cualquiera de las obligaciones establecidas en el contrato, especialmente por mora de una factura o crédito fiscal por servicio prestado, por casos establecidos en la ley y su respectivo reglamento. La cancelación en el servicio por parte de "EL CLIENTE" no lo exime del pago de las cantidades adeudadas. Este deberá cubrirlas al 100% al momento de la cancelación; así mismo cancelará la suma de los meses pendientes cuando falte para la finalización del contrato; de igual manera permitir el retiro del equipo suministrado por el PROVEEDOR y de las instalaciones realizadas en el domicilio de "EL CLIENTE".</p>

        <p><strong>7. EQUIPO ENTREGADO EN COMODATO:</strong> a) Recibí de parte de OMNIVISION-OMNICOM en entera satisfacción y en calidad de comodato el equipo que permitirá recibir el servicio de cable tv e internet. b) Es mi responsabilidad el mantenimiento y cuidado del equipo por uso normal durante el tiempo del contrato vigente. c) El equipo se encontrará en la dirección proporcionada por el cliente. d) Me comprometo a devolver el equipo al final del plazo en buen estado. e) En caso de hurto, robo o pérdida del equipo notificaré a OMNIVISION-OMNICOM para el bloqueo del servicio. f) Para reposición del equipo, el cliente podrá solicitar la reposición pagando el valor total del equipo. g) El cliente no podrá arrendar ni ceder los derechos emanados del equipo.</p>

        <p><strong>8. CONDICIONES ESPECIALES DE CONTRATACION DE SERVICIOS DE INTERNET:</strong> a) El cliente podrá utilizar el servicio únicamente desde el número de protocolo de interconexión asignada por la empresa. b) El servicio se prestará en forma continua, las 24 horas del día, todo el año; salvo mora en el pago o caso fortuito de fuerza mayor. c) El cliente garantiza las instalaciones eléctricas, equipos de protección y equipo informático adecuado.</p>

        <p><strong>9. OBLIGACIONES DE OMNIVISION-OMNICOM:</strong> a) Suministrar el servicio de Internet y Cable TV, bajo las condiciones establecidas en el presente contrato. b) Obligaciones Legales indicadas en las leyes aplicables. c) Brindar respuesta clara y oportuna a reclamos del cliente. d) Reintegrar en próxima factura cantidades cobradas de forma contraria a lo pactado.</p>

        <p><strong>10. OBLIGACIONES DEL CLIENTE:</strong> a) Pagar puntualmente los cargos por la prestación de servicios, así como los recargos por pagos tardíos. b) No utilizar las redes de telecomunicaciones para actividades contrarias a la ley. c) Cuidado de los equipos, aceptando la responsabilidad por su buen uso y conservación.</p>

        <p><strong>11. ES RESPONSABILIDAD DEL CLIENTE:</strong> El cuido de la Red y Equipo que la empresa Omnivisión proporciona; luego de su instalación; ya que no nos haremos responsables por el daño que sea causado con dolo por la parte contratante, siempre y cuando el personal encargado lo manifieste y así mismo se le hará saber al cliente, luego del diagnóstico presencial que nuestro personal realice en su domicilio.</p>

        <p><strong>PAGARE SIN PROTESTO:</strong> Pagaré en forma incondicional a la orden de OMNIVISION-OMNICOM la cantidad establecida en el presente contrato. En caso de no ser pagado a su vencimiento, pagaré además el interés moratorio del % mensual. Para los efectos legales me someto a los tribunales de la ciudad de Chalatenango.</p>

        <p><em>Nota: El uso de la señal de telecomunicaciones es exclusivo para la persona que lo contrata. Por ningún motivo podrá compartir la señal, de lo contrario se suspenderá el servicio y será demandado por los daños correspondientes a nuestra empresa.</em></p>';
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
