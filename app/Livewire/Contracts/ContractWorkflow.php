<?php

namespace App\Livewire\Contracts;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Plan;
use App\Models\PlanRule;
use App\Models\Ticket;
use App\Models\WorkOrder;
use App\Models\Zone;
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
    public $documentsProgress = [];

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
    public $customer_type = '';
    public $price;
    public $effective_price = 0;
    public $availablePlans = [];
    public $availableZones = [];
    public $installation_cost = null;
    public $payment_day = '';
    public $payment_date = null;

    // --- Promociones (meses gratis / doble velocidad) ---
    public $promo_free_months = 0;
    public $promo_pay_months = 0;
    public $promo_total_months = 0;
    public $promo_double_speed = false;
    public $promo_display_speed = '';
    public $promo_original_speed = '';

    // ─── TV extra ───
    public $extra_tvs = 0;
    public $tv_install_fee = 0;
    public $monthly_extra_fee = 0;

    // ─── Datos comerciales del contrato ───
    public $contract_type = 'nuevo';
    public $term_months = 24;
    public $benefit = '';
    public $benefitManuallySet = false;

    // ─── Beneficios interactivos ───
    public array $availableBenefits = [];
    public array $selectedBenefits = [];

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

    // ─── Portal unificado del cliente ───
    public $portal_link = null;

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
    public $createdWorkOrderCode = null;
    public $createdWorkOrderId = null;

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
                'customer_type' => 'required|in:residencial,pyme,corporativo',
                'client_billing_address' => 'required|string|max:500',
            ],
            2 => [
                'plan_id' => 'required|exists:plans,id',
                'price' => 'required|numeric|min:0',
                'contract_type' => 'required|in:nuevo,reconexion,renovacion',
                'term_months' => 'required|integer|min:1|max:60',
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
        'customer_type.required' => 'Debe seleccionar el tipo de servicio (Residencial, Pyme o Corporativo).',
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
            ->get();
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
                $this->quickReferencePlans = Plan::where('is_active', true)->get();
            }

            // ─── Si el ticket ya tiene plan, cargarlo ───
            if ($ticket->plan_id) {
                $this->plan_id = $ticket->plan_id;
                if ($this->zone_id) {
                    $this->updateEffectivePrice();
                }
            }

            $this->recalculateBenefits();
            $this->refreshPromotions();

            // Cargar la firma del cliente si ya existe (portal o enlace único de firma)
            if ($client->client_signature_data) {
                $this->client_signature_data = $client->client_signature_data;
                $this->showClientSignature = true;
            }

            // Precargar el contrato del ticket si ya existe (para descargar PDF tras recargar)
            $existingContract = $ticket->contract;
            if ($existingContract) {
                $this->contract_id = $existingContract->id;
                $this->contractDigitalCode = $existingContract->contract_digital_code;
                $this->payment_date = $existingContract->payment_date?->format('Y-m-d');
                $this->payment_day = $existingContract->payment_day;
                $this->extra_tvs = (int) ($existingContract->extra_tvs ?? 0);
                $this->tv_install_fee = (float) ($existingContract->tv_install_fee ?? 0);
                $this->monthly_extra_fee = (float) ($existingContract->monthly_extra_fee ?? 0);
                $this->customer_type = $existingContract->customer_type ?? '';
            }

            // ─── Ticket promovido desde verificación en campo ───
            // El contrato ya fue generado automáticamente por el técnico con el precio
            // de instalación congelado (contract_price_snapshot). Se precarga para que
            // el agente de contratos solo complete lo que falta.
            if ($ticket->promotion_status === 'promoted') {
                $this->service_type = $existingContract?->service_type ?? 'instalacion';
                $this->installation_cost = $ticket->contract_price_snapshot
                    ?? $existingContract?->installation_cost
                    ?? null;

                if (!$this->plan_id && $existingContract?->plan_id) {
                    $this->plan_id = $existingContract->plan_id;
                }
                if ($existingContract?->price) {
                    $this->price = $existingContract->price;
                    $this->effective_price = (float) $existingContract->price;
                }
            }
        }

        $this->contract_terms = $this->getDefaultTerms();

        // Restaurar documento del draft (persistencia al recargar)
        $draft = session()->get($this->draftKey(), []);
        if (is_array($draft)) {
            if (isset($draft['uploaded_documents']) && is_array($draft['uploaded_documents'])) {
                $this->uploadedDocuments = $draft['uploaded_documents'];
            } elseif (isset($draft['dui_front']) || isset($draft['dui_back']) || isset($draft['receipt']) || isset($draft['fachada'])) {
                // Compatibilidad con drafts viejos (el draft era el array de documentos directo)
                $this->uploadedDocuments = $draft;
            }
            if (!empty($draft['sales_rep_signature'])) {
                $this->sales_rep_signature_data = $draft['sales_rep_signature'];
                $this->showSalesRepSignature = true;
            }
            if (!empty($draft['customer_type'])) {
                $this->customer_type = $draft['customer_type'];
            }
            if (!empty($draft['payment_date'])) {
                $this->payment_date = $draft['payment_date'];
            }
            if (!empty($draft['payment_day'])) {
                $this->payment_day = $draft['payment_day'];
            }
        }

        $this->loadClientUploadedDocs();
        $this->computeDocumentsProgress();
    }

    private function draftKey(): string
    {
        return 'contract_workflow_docs_' . ($this->ticket_id ?? 'no-ticket');
    }

    private function persistDraft(): void
    {
        session()->put($this->draftKey(), [
            'uploaded_documents' => $this->uploadedDocuments,
            'sales_rep_signature' => $this->sales_rep_signature_data,
            'customer_type' => $this->customer_type,
            'payment_date' => $this->payment_date,
            'payment_day' => $this->payment_day,
        ]);
    }

    public function updatedCustomerType($value)
    {
        $this->persistDraft();
    }

    public function updatedLatitude($value)
    {
        $this->persistClientCoordinates();
    }

    public function updatedLongitude($value)
    {
        $this->persistClientCoordinates();
    }

    private function persistClientCoordinates(): void
    {
        if (!$this->client_id) return;

        Client::where('id', $this->client_id)->update([
            'latitude' => $this->latitude !== '' && $this->latitude !== null ? $this->latitude : null,
            'longitude' => $this->longitude !== '' && $this->longitude !== null ? $this->longitude : null,
        ]);
    }

    public function updatedStep($value)
    {
        // Al entrar al paso de firma, refrescar la firma del cliente (portal o enlace único)
        if ((int) $value === 4 && $this->client_id) {
            $client = Client::find($this->client_id);
            if ($client && $client->client_signature_data) {
                $this->client_signature_data = $client->client_signature_data;
                $this->showClientSignature = true;
            }
        }
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
            // Validar que la firma no esté en blanco (canvas vacío).
            $hasValidSignature = $this->signature_link
                || ($this->client_signature_data && $this->isRealSignature($this->client_signature_data));

            if (!$hasValidSignature) {
                $this->dispatch('show-toast', type: 'error', message: 'Debe capturar la firma del cliente (no puede estar en blanco).');
                return;
            }

            // Cuando se completa la firma, ir a preview
            $this->step = 5;
        }
    }

    /**
     * Verifica que un data URL de firma contenga trazos reales y no sea una imagen en blanco.
     */
    private function isRealSignature(string $dataUrl): bool
    {
        $pos = strpos($dataUrl, 'base64,');
        if ($pos === false) {
            return false;
        }

        $raw = base64_decode(substr($dataUrl, $pos + 7));
        if ($raw === false || strlen($raw) < 200) {
            return false;
        }

        $image = @imagecreatefromstring($raw);
        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 2 || $height < 2) {
            imagedestroy($image);
            return false;
        }

        // Contar píxeles no blancos para detectar un canvas vacío
        $hasInk = false;
        $sampleStep = max(1, (int) ceil(($width * $height) / 5000));
        for ($y = 0; $y < $height && !$hasInk; $y += $sampleStep) {
            for ($x = 0; $x < $width; $x += $sampleStep) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if ($r < 245 || $g < 245 || $b < 245) {
                    $hasInk = true;
                    break;
                }
            }
        }

        imagedestroy($image);
        return $hasInk;
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    private $_cachedZone = null;

    private function getZoneModel(): ?Zone
    {
        if ($this->_cachedZone === null && $this->zone_id) {
            $this->_cachedZone = Zone::find($this->zone_id);
        }
        return $this->_cachedZone;
    }

    // ─── Planes de Referencia (Cliente Potencial) ───

    public function addPlanReference($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan)
            return;

        $zone = $this->getZoneModel();
        $price = $zone ? $zone->getEffectivePriceForPlan($plan) : $plan->base_price;

        $this->plan_id = $plan->id;
        $this->price = $price ?? $plan->base_price;
        $this->effective_price = $this->price;
        $this->benefitManuallySet = false;
        $this->loadAvailableBenefits();
        $this->selectedBenefits = array_keys($this->availableBenefits);
        $this->benefit = $this->getAppliedBenefits();

        $this->dispatch('show-toast', type: 'success', message: "Plan «{$plan->name}» seleccionado.");
    }

    // ─── Step 2: Plan ───

    private function benefitLabel(string $ruleKey, mixed $value = null): string
    {
        $label = match ($ruleKey) {
            'free_installation' => 'Instalación gratuita',
            'double_speed' => 'Doble velocidad',
            'discount_months' => 'Descuento por prepago',
            'festive_eligible' => 'Elegible para promos festivas',
            default => $ruleKey,
        };

        if ($ruleKey === 'discount_months' && is_array($value)) {
            $pay = $value['pay'] ?? '';
            $total = $value['total'] ?? '';
            if ($pay && $total) {
                $label = "Descuento: paga {$pay} meses, recibe {$total}";
            }
        }

        return $label;
    }

    public function getAppliedBenefits(): string
    {
        $labels = array_map(fn($k) => $this->benefitLabel($k), $this->selectedBenefits);
        return implode(', ', $labels);
    }

    public function loadAvailableBenefits(): void
    {
        if (!$this->plan_id || !$this->term_months) {
            $this->availableBenefits = [];
            return;
        }

        $rules = PlanRule::getEffectiveRules($this->plan_id, $this->zone_id ?: null, $this->term_months);
        $this->availableBenefits = [];
        foreach ($rules as $key => $value) {
            $this->availableBenefits[$key] = [
                'label' => $this->benefitLabel($key, $value),
            ];
        }
    }

    public function toggleBenefit(string $ruleKey): void
    {
        $this->benefitManuallySet = false;

        if (in_array($ruleKey, $this->selectedBenefits, true)) {
            $this->selectedBenefits = array_values(array_filter($this->selectedBenefits, fn($k) => $k !== $ruleKey));
        } else {
            $this->selectedBenefits[] = $ruleKey;
        }

        $this->benefit = $this->getAppliedBenefits();
    }

    public function resetBenefits(): void
    {
        $this->benefitManuallySet = false;
        $this->selectedBenefits = array_keys($this->availableBenefits);
        $this->benefit = $this->getAppliedBenefits();
    }

    private function defaultInstallationCost(): ?float
    {
        if (!$this->ticket_id) {
            return null;
        }
        $tk = \App\Models\Ticket::find($this->ticket_id);
        if (!$tk) {
            return null;
        }
        try {
            $fee = app(\App\Services\VerificationPricingService::class)->installFeeFor($tk);
            return (float) ($fee['fee'] ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deriveServiceContracted(): string
    {
        // Prioridad: derivar del tipo de servicio del plan seleccionado.
        if ($this->plan_id) {
            $planService = \App\Models\Plan::where('id', $this->plan_id)->value('service_type');
            if ($planService) {
                return match ($planService) {
                    'internet' => 'internet',
                    'cable' => 'cable',
                    'internet_cable' => 'cable_internet',
                    default => $planService,
                };
            }
        }

        return match ($this->service_type) {
            'internet' => 'internet',
            'cable' => 'cable',
            'internet_cable' => 'cable_internet',
            default => $this->service_type ?? '',
        };
    }

    public function updatedPlanId($value)
    {
        $this->benefitManuallySet = false;
        $this->updateEffectivePrice();
        $this->loadAvailableBenefits();
        $this->selectedBenefits = array_keys($this->availableBenefits);
        $this->benefit = $this->getAppliedBenefits();
        $this->refreshPromotions();

        // Persistir el plan en el ticket para que no se pierda al recargar la página
        if ($this->ticket_id) {
            Ticket::where('id', $this->ticket_id)->update(['plan_id' => $value ?: null]);
        }
    }

    public function updatedZoneId($value)
    {
        $this->benefitManuallySet = false;
        $this->_cachedZone = null;
        $this->updateEffectivePrice();
        $this->refreshBenefitsFromAvailable();
        $this->refreshPromotions();
    }

    public function updatedTermMonths($value)
    {
        $this->benefitManuallySet = false;
        $this->refreshBenefitsFromAvailable();
        $this->refreshPromotions();
    }

    /**
     * Al seleccionar la fecha de pago en el workflow, se deriva el día del mes (payment_day).
     */
    public function updatedPaymentDate($value)
    {
        if ($value) {
            try {
                $date = \Illuminate\Support\Carbon::parse($value);
                $this->payment_day = (string) $date->day;
            } catch (\Throwable $e) {
                // Si la fecha es inválida, no se deriva nada.
            }
        }

        // Persistir en sesión para que no se pierda al recargar.
        $this->persistDraft();

        // Persistir la fecha y el día de pago para que no se pierdan al recargar la página.
        if ($this->ticket_id) {
            $contract = \App\Models\Contract::where('ticket_id', $this->ticket_id)->first();
            if ($contract) {
                $contract->update([
                    'payment_date' => $this->payment_date ?: null,
                    'payment_day' => $this->payment_day ?: null,
                ]);
            }
        }
    }

    /**
     * Recalcula las promociones (meses gratis y doble velocidad) según plan+zona+plazo.
     */
    public function refreshPromotions(): void
    {
        $promo = app(\App\Services\PromotionService::class);
        $planId = $this->plan_id ?: null;
        $zoneId = $this->zone_id ?: null;
        $term = (int) $this->term_months;

        $free = $promo->freeMonths($planId, $zoneId, $term);
        $this->promo_free_months = $free['free'];
        $this->promo_pay_months = $free['pay'];
        $this->promo_total_months = $free['total'];

        $speed = $promo->effectiveSpeed($planId, $zoneId, $term);
        $this->promo_double_speed = $speed['doubled'];
        $this->promo_original_speed = $speed['original_text'];
        $this->promo_display_speed = $speed['effective'] > 0
            ? $speed['effective'] . ' Mbps' . ($speed['doubled'] ? ' (doble)' : '')
            : $speed['original_text'];
    }

    /**
     * Al cambiar la cantidad de TVs extra, recalcula los cargos:
     * - $6 de instalación por TV (cargo único)
     * - $1 mensual recurrente por TV
     */
    public function updatedExtraTvs($value)
    {
        $count = max(0, (int) $value);
        $this->extra_tvs = $count;

        // Valores configurables por zona desde las reglas de contrato (TV extra)
        $fees = \App\Services\TvExtraFees::forZone($this->zone_id ?: null);
        // Cargo de instalación: $6 POR CADA TV extra.
        $this->tv_install_fee = $count * $fees['install_fee'];
        // Recargo mensual: FIJO (+$1) sin importar cuántas TVs extra haya.
        $this->monthly_extra_fee = $count * ($fees['monthly_fee'] ?? 1);
    }

    public function getMonthlyTotal(): float
    {
        return (float) ($this->price ?? 0) + (float) $this->monthly_extra_fee;
    }

    public function getInstallTotal(): float
    {
        return (float) ($this->installation_cost ?? 0) + (float) $this->tv_install_fee;
    }

    /**
     * Desglose de la instalación por distancia de la OT de verificación asociada.
     * Devuelve base, metros extra, recargo y la instalación desglosada para que
     * el agente entienda el costo de instalación.
     *
     * @return array|null
     */
    public function getVerificationBreakdownProperty(): ?array
    {
        if (!$this->ticket_id) {
            return null;
        }
        $ot = WorkOrder::where('ticket_id', $this->ticket_id)
            ->where(function ($q) {
                $q->where('service_type', 'verificacion_instalacion')
                    ->orWhereNotNull('drop_distance');
            })
            ->first();

        if (!$ot || !$ot->drop_distance) {
            return null;
        }

        $service = app(\App\Services\VerificationPricingService::class);
        if (!$ot->ticket) {
            return null;
        }

        $fee = $service->installFeeFor($ot->ticket);
        $covered = (int) ($fee['covered_meters'] ?? 150);
        $base = (float) ($fee['fee'] ?? 0);
        $excessPer = (float) ($ot->precio_por_metro ?? $fee['excess_per_50m'] ?? 0);
        $drop = (float) $ot->drop_distance;

        $blocks = $drop > $covered ? (int) ceil(($drop - $covered) / 50) : 0;
        $excessTotal = $blocks * $excessPer;

        // Si hay campaña de instalación gratis aplicada, el costo también es 0.
        $manual = (float) ($ot->verification_price ?? 0);

        return [
            'distance' => $drop,
            'covered' => $covered,
            'base' => $base,
            'excess_per_50m' => $excessPer,
            'blocks' => $blocks,
            'excess_total' => $excessTotal,
            'manual_price' => $manual,
            'subtotal' => $base + $excessTotal,
        ];
    }

    private function refreshBenefitsFromAvailable(): void
    {
        $this->loadAvailableBenefits();
        // Mantener solo los que siguen disponibles, descartar los que ya no aplican
        $this->selectedBenefits = array_values(array_intersect(
            $this->selectedBenefits,
            array_keys($this->availableBenefits)
        ));
        $this->benefit = $this->getAppliedBenefits();
    }

    public function updatedBenefit($value)
    {
        $this->benefitManuallySet = !empty($value);
    }

    private function recalculateBenefits(): void
    {
        if ($this->benefitManuallySet) return;

        $this->loadAvailableBenefits();
        $this->selectedBenefits = array_keys($this->availableBenefits);
        $this->benefit = $this->getAppliedBenefits();
    }

    public function updateEffectivePrice()
    {
        if (!$this->plan_id) {
            $this->effective_price = 0;
            $this->price = 0;
            return;
        }

        $plan = Plan::find($this->plan_id);
        if (!$plan)
            return;

        $zone = $this->getZoneModel();
        $this->effective_price = $zone ? (float) $zone->getEffectivePriceForPlan($plan) : (float) $plan->base_price;
        $this->price = $this->effective_price;
    }

    public function getPlanPriceDetailProperty()
    {
        if (!$this->plan_id)
            return null;

        $plan = Plan::find($this->plan_id);
        if (!$plan)
            return null;

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
        if (!$file)
            return;

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

        $this->computeDocumentsProgress();
        $this->persistDraft();
        $this->dispatch('show-toast', type: 'success', message: 'Documento subido correctamente.');
    }

    public function updatedDuiFront()
    {
        $this->uploadDocument('dui_front');
    }

    public function updatedDuiBack()
    {
        $this->uploadDocument('dui_back');
    }

    public function updatedReceipt()
    {
        $this->uploadDocument('receipt');
    }

    public function updatedFachada()
    {
        $this->uploadDocument('fachada');
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

        $this->computeDocumentsProgress();
        $this->persistDraft();
        $this->dispatch('show-toast', type: 'info', message: 'Documento eliminado.');
    }

    private function computeDocumentsProgress(): void
    {
        $required = ['dui_front', 'dui_back', 'receipt', 'fachada'];
        $optional = [];

        $uploaded = array_keys($this->uploadedDocuments);
        $clientTypes = array_column($this->clientUploadedDocs, 'type');
        $allUploaded = array_unique(array_merge($uploaded, $clientTypes));

        $this->documentsProgress = [
            'required_completed' => empty(array_diff($required, $allUploaded)),
            'completed_required' => count(array_intersect($required, $allUploaded)),
            'total_required' => count($required),
            'completed_optional' => count(array_intersect($optional, $allUploaded)),
            'total' => count($allUploaded),
        ];
    }

    // ─── Step 4: Firma ───

    public function saveClientSignature($signatureData)
    {
        $this->client_signature_data = $signatureData;
        $this->showClientSignature = true;

        // Persistir en el cliente para que la firma presencial quede registrada igual que la del portal
        if ($this->client_id) {
            Client::where('id', $this->client_id)->update(['client_signature_data' => $signatureData]);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Firma del cliente capturada.');
    }

    public function resetClientSignature()
    {
        $this->client_signature_data = null;
        $this->showClientSignature = false;

        // Limpiar también la firma del cliente para que el portal permita volver a firmar
        if ($this->client_id) {
            Client::where('id', $this->client_id)->update([
                'client_signature_data' => null,
                'signature_approved' => false,
            ]);
        }

        $this->dispatch('show-toast', type: 'info', message: 'Firma reiniciada. El cliente deberá firmar nuevamente.');
    }

    public function saveSalesRepSignature($signatureData)
    {
        $this->sales_rep_signature_data = $signatureData;
        $this->showSalesRepSignature = true;
        $this->persistDraft();
        $this->dispatch('show-toast', type: 'success', message: 'Tu firma ha sido capturada.');
    }

    public function resetSalesRepSignature()
    {
        $this->sales_rep_signature_data = null;
        $this->showSalesRepSignature = false;
        $this->persistDraft();
        $this->dispatch('show-toast', type: 'info', message: 'Firma del agente reiniciada.');
    }

    public function generatePortalLink()
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
        if ($client->portal_token && $client->portal_token_expires_at && $client->portal_token_expires_at->greaterThan($now)) {
            $this->portal_link = route('public.contract.portal', ['token' => $client->portal_token]);
            $this->dispatch('show-toast', type: 'success', message: 'Enlace vigente reutilizado.');
            return;
        }

        $client->update([
            'portal_token' => (string) Str::random(64),
            'portal_token_expires_at' => $now->copy()->addHours(24),
        ]);

        $this->portal_link = route('public.contract.portal', ['token' => $client->portal_token]);

        $this->dispatch('show-toast', type: 'success', message: 'Enlace del portal generado. Compártelo con el cliente.');
    }

    public function getPortalWhatsAppUrl(): ?string
    {
        $client = Client::find($this->client_id);
        if (!$client || !$client->phone)
            return null;

        if (!$this->portal_link) {
            $this->generatePortalLink();
        }
        if (!$this->portal_link)
            return null;

        $phone = preg_replace('/\D/', '', $client->phone);
        if (strlen($phone) === 8) {
            $phone = '503' . $phone;
        } elseif (strlen($phone) === 9 && $phone[0] === '0') {
            $phone = '503' . substr($phone, 1);
        }

        $message = "Hola, soy de Omnivisión. Para finalizar tu contrato, ingresá a este enlace para subir tus documentos, compartir tu ubicación y firmar digitalmente:\n\n";
        $message .= $this->portal_link . "\n\n";
        $message .= "⚠️ El enlace expira en 24 horas.";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function sendPortalViaWhatsApp()
    {
        $url = $this->getPortalWhatsAppUrl();
        if (!$url) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente no tiene un número de teléfono registrado.');
            return;
        }

        $this->dispatch('open-whatsapp', url: $url);
    }

    public function generateSignatureLink()
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
        if ($client->signature_token && $client->signature_token_expires_at && $client->signature_token_expires_at->greaterThan($now)) {
            $this->signature_link = route('public.contract.sign', ['token' => $client->signature_token]);
            $this->dispatch('show-toast', type: 'success', message: 'Enlace vigente reutilizado.');
            return;
        }

        $client->update([
            'signature_token' => (string) Str::random(64),
            'signature_token_expires_at' => $now->copy()->addHours(24),
        ]);

        $this->signature_link = route('public.contract.sign', ['token' => $client->signature_token]);

        $this->dispatch('show-toast', type: 'success', message: 'Enlace de firma generado. Compártelo con el cliente.');
    }

    public function getSignatureWhatsAppUrl(): ?string
    {
        $client = Client::find($this->client_id);
        if (!$client || !$client->phone)
            return null;

        if (!$this->signature_link) {
            $this->generateSignatureLink();
        }
        if (!$this->signature_link)
            return null;

        $phone = preg_replace('/\D/', '', $client->phone);
        if (strlen($phone) === 8) {
            $phone = '503' . $phone;
        } elseif (strlen($phone) === 9 && $phone[0] === '0') {
            $phone = '503' . substr($phone, 1);
        }

        $message = "Hola, soy de Omnivisión. Para finalizar tu contrato necesitamos tu firma electrónica. Hacé clic en este enlace y firmá digitalmente:\n\n";
        $message .= $this->signature_link . "\n\n";
        $message .= "⚠️ El enlace expira en 24 horas.";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    public function sendSignatureViaWhatsApp()
    {
        $url = $this->getSignatureWhatsAppUrl();
        if (!$url) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente no tiene un número de teléfono registrado.');
            return;
        }

        $this->dispatch('open-whatsapp', url: $url);
    }

    public function refreshClientSignature()
    {
        if (!$this->client_id)
            return;
        $client = Client::find($this->client_id);
        if ($client && $client->client_signature_data) {
            $this->client_signature_data = $client->client_signature_data;
            $this->showClientSignature = true;
            $this->dispatch('show-toast', type: 'success', message: 'Firma del cliente actualizada.');
        }
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

        $contractData = [
            'client_id' => $this->client_id,
            'ticket_id' => $this->ticket_id,
            'plan_id' => $this->plan_id ?: null,
            'zone_id' => $this->zone_id ?: null,
            'service_type' => $this->service_type,
            'customer_type' => $this->customer_type,
            'price' => $this->price,
            'installation_address' => $this->installation_address,
            'latitude' => $this->latitude ?: null,
            'longitude' => $this->longitude ?: null,
            'contract_terms' => $this->contract_terms,
            'contract_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'created_by' => Auth::id(),
            'contract_type' => $this->contract_type,
            'service_contracted' => $this->deriveServiceContracted(),
            'speed' => $this->plan_id ? (Plan::where('id', $this->plan_id)->value('speed') ?? null) : null,
            'installation_cost' => $this->installation_cost ?: $this->defaultInstallationCost(),
            'term_months' => $this->term_months,
            'benefit' => $this->benefit,
            'extra_tvs' => $this->extra_tvs,
            'tv_install_fee' => $this->tv_install_fee,
            'monthly_extra_fee' => $this->monthly_extra_fee,
            'payment_day' => $this->payment_day ?: null,
            'payment_date' => $this->payment_date ?: null,
        ];

        // Si el contrato ya fue pre-generado (ticket promovido desde verificación),
        // se actualiza en lugar de crear un duplicado.
        if ($this->contract_id) {
            $contract = Contract::find($this->contract_id);
            $contract->update($contractData);
        } else {
            $contract = Contract::create($contractData);
        }

        $this->contract_id = $contract->id;
        $this->contractDigitalCode = $contract->contract_digital_code;

        // Registrar cobros de TVs extra (cargo único + recargo mensual recurrente)
        $this->syncTvExtraCharges($contract);

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

        // Si el cliente firmó vía enlace público, transferir la firma
        $client = Client::find($this->client_id);
        $clientSignature = $client?->client_signature_data ?? $this->client_signature_data;

        if ($clientSignature) {
            $sigService->saveSignature($contract, 'client', $clientSignature);
        }

        if ($this->sales_rep_signature_data) {
            $sigService->saveSignature($contract, 'sales_rep', $this->sales_rep_signature_data);
        }

        // Limpiar token de firma del cliente
        if ($client) {
            $client->update([
                'signature_token' => null,
                'signature_token_expires_at' => null,
                'client_signature_data' => null,
                'signature_approved' => false,
            ]);
        }

        // Generar PDF
        $pdfService = app(ContractPdfService::class);
        $pdfService->generate($contract);

        // El ticket NO se resuelve aquí: queda esperando la instalación.
        // Se cerrará cuando el técnico complete la OT (completeWorkOrder).
        if ($this->ticket_id) {
            Ticket::where('id', $this->ticket_id)->update([
                'contracts_ended_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        // Crear la OT de instalación (la asigna el supervisor). Solo se reutiliza
        // si ya existe una OT del mismo service_type (no la de verificación).
        if ($this->ticket_id) {
            $workOrder = WorkOrder::where('ticket_id', $this->ticket_id)
                ->where('service_type', $contract->service_type)
                ->first();

            if (!$workOrder) {
                $ticket = Ticket::find($this->ticket_id);
                $woService = app(\App\Services\WorkOrderService::class);
                $workOrder = $woService->createFromContract($contract, [
                    'code' => $woService->generateCode(Auth::user(), $ticket),
                    'sla_started_at' => now(),
                    'requires_noc' => $ticket?->requires_noc ?? false,
                    'description' => 'Instalación - Contrato #' . $contract->id,
                ]);
            }

            if ($workOrder) {
                $this->createdWorkOrderCode = $workOrder->code;
                $this->createdWorkOrderId = $workOrder->id;
            }
        }

        $this->step = 5;
        session()->forget($this->draftKey());
        $this->dispatch('show-toast', type: 'success', message: 'Contrato #' . $contract->contract_digital_code . ' creado correctamente.');
    }

    /**
     * Sincroniza los cobros de TVs extra del contrato.
     * - Cargo único de instalación ($6 por TV) -> no recurrente.
     * - Recargo mensual (+$1 por TV) -> recurrente mensual.
     */
    protected function syncTvExtraCharges(Contract $contract): void
    {
        // Limpiar cobros previos de TV extra para evitar duplicados al re-finalizar
        $contract->charges()->where('type', 'extra_tv')->delete();

        $count = max(0, (int) $this->extra_tvs);
        if ($count <= 0) {
            return;
        }

        if ($this->tv_install_fee > 0) {
            $contract->charges()->create([
                'client_id' => $contract->client_id,
                'type' => 'extra_tv',
                'description' => "Instalación de TV extra x{$count}",
                'amount' => $this->tv_install_fee,
                'is_recurring' => false,
                'recurring_period' => null,
                'quantity' => $count,
            ]);
        }

        if ($this->monthly_extra_fee > 0) {
            $contract->charges()->create([
                'client_id' => $contract->client_id,
                'type' => 'extra_tv',
                'description' => "Recargo mensual TV extra x{$count}",
                'amount' => $this->monthly_extra_fee,
                'is_recurring' => true,
                'recurring_period' => 'monthly',
                'quantity' => $count,
            ]);
        }
    }

    public function downloadPdf()
    {
        if (!$this->contract_id && $this->ticket_id) {
            $contract = Contract::where('ticket_id', $this->ticket_id)->first();
            if ($contract) {
                $this->contract_id = $contract->id;
                $this->contractDigitalCode = $contract->contract_digital_code;
            }
        }

        if (!$this->contract_id) {
            $this->dispatch('show-toast', type: 'error', message: 'No hay contrato para descargar.');
            return;
        }

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
        if (!$client || !$client->phone)
            return null;

        // Asegurar que el enlace GPS esté generado
        if (!$this->gps_link) {
            $this->generateGpsLink();
        }
        if (!$this->gps_link)
            return null;

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
        if (!$this->client_id)
            return;

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
        if (!$client)
            return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $i => $d) {
            if ($d['type'] === $type) {
                Storage::disk('s3')->delete($d['path']);
                unset($docs[$i]);
                break;
            }
        }
        $client->update([
            'uploaded_docs' => array_values($docs),
            'portal_docs_approved' => false,
        ]);
        $this->clientUploadedDocs = $client->fresh()->uploaded_docs ?? [];

        $labels = ['dui_front' => 'DUI (Frente)', 'dui_back' => 'DUI (Reverso)', 'receipt' => 'Recibo de luz', 'fachada' => 'Foto de Fachada'];
        $this->dispatch('show-toast', type: 'info', message: $labels[$type] . ' rechazado.');
    }

    public function rejectClientDocs()
    {
        $client = Client::find($this->client_id);
        if (!$client)
            return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $d) {
            Storage::disk('s3')->delete($d['path']);
        }

        $client->update([
            'uploaded_docs' => [],
            'portal_docs_approved' => false,
        ]);
        $this->clientUploadedDocs = [];

        $this->dispatch('show-toast', type: 'info', message: 'Documentos rechazados. El enlace sigue vigente para que el cliente los vuelva a subir.');
    }

    public function rejectClientCoordinates()
    {
        $client = Client::find($this->client_id);
        if (!$client) return;

        $client->update([
            'latitude' => null,
            'longitude' => null,
            'coordinates_approved' => false,
        ]);
        $this->dispatch('show-toast', type: 'info', message: 'Coordenadas del cliente rechazadas.');
    }

    public function approveClientCoordinates()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        if (!$client) return;

        if (!$client->latitude || !$client->longitude) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente aún no ha enviado coordenadas.');
            return;
        }

        $client->update(['coordinates_approved' => true]);
        $this->dispatch('show-toast', type: 'success', message: 'Coordenadas aprobadas para la instalación.');
    }

    public function rejectClientSignature()
    {
        $client = Client::find($this->client_id);
        if (!$client) return;

        $client->update([
            'client_signature_data' => null,
            'signature_approved' => false,
        ]);
        $this->dispatch('show-toast', type: 'info', message: 'Firma del cliente rechazada.');
    }

    public function approveClientSignature()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        if (!$client) return;

        if (!$client->client_signature_data) {
            $this->dispatch('show-toast', type: 'error', message: 'El cliente aún no ha firmado.');
            return;
        }

        $client->update(['signature_approved' => true]);
        $this->dispatch('show-toast', type: 'success', message: 'Firma aprobada. Ya podés generar el contrato.');
    }

    public function rejectAllClientDocs()
    {
        $client = Client::find($this->client_id);
        if (!$client)
            return;

        $docs = $client->uploaded_docs ?? [];
        foreach ($docs as $d) {
            Storage::disk('s3')->delete($d['path']);
        }

        $newToken = (string) Str::random(64);
        $expiresAt = now()->copy()->addHours(24);

        $client->update([
            'uploaded_docs' => [],
            'docs_token' => null,
            'docs_token_expires_at' => null,
            'gps_token' => null,
            'gps_token_expires_at' => null,
            'signature_token' => null,
            'signature_token_expires_at' => null,
            'portal_token' => $newToken,
            'portal_token_expires_at' => $expiresAt,
            'portal_docs_approved' => false,
            'latitude' => null,
            'longitude' => null,
            'coordinates_approved' => false,
            'client_signature_data' => null,
            'signature_approved' => false,
        ]);
        $this->clientUploadedDocs = [];
        $this->docs_link = null;
        $this->portal_link = route('public.contract.portal', ['token' => $newToken]);

        $this->dispatch('show-toast', type: 'success', message: 'Todo rechazado. Se generó un enlace nuevo para el cliente.');
    }

    // ─── Documentos: Enlace público de subida ───

    public function loadClientUploadedDocs()
    {
        if (!$this->client_id)
            return;
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
        if (!$client || !$client->phone)
            return null;

        if (!$this->docs_link) {
            $this->generateDocsLink();
        }
        if (!$this->docs_link)
            return null;

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
        if (!$path)
            return null;
        try {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(10));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function refreshUploadedDocs()
    {
        if (!$this->client_id)
            return;

        $client = Client::find($this->client_id);
        if ($client) {
            $this->clientUploadedDocs = $client->uploaded_docs ?? [];
            $this->dispatch('show-toast', type: 'success', message: 'Documentos actualizados desde el cliente.');
        }
    }

    public function approveClientDocs()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        if (!$client) return;

        $required = ['dui_front', 'dui_back', 'receipt', 'fachada'];
        $uploadedTypes = array_column($client->uploaded_docs ?? [], 'type');
        $missing = array_diff($required, $uploadedTypes);

        if (!empty($missing)) {
            $this->dispatch('show-toast', type: 'error', message: 'Faltan documentos requeridos para aprobar.');
            return;
        }

        $client->update(['portal_docs_approved' => true]);
        $this->dispatch('show-toast', type: 'success', message: 'Documentos aprobados. El cliente ya puede firmar.');
    }


    private function getDefaultTerms(): string
    {
        $defaultTerms = '
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

        <p><strong>PAGARE SIN PROTESTIO:</strong> Pagaré en forma incondicional a la orden de OMNIVISION-OMNICOM la cantidad establecida en el presente contrato. En caso de no ser pagado a su vencimiento, pagaré además el interés moratorio del % mensual. Para los efectos legales me someto a los tribunales de la ciudad de Chalatenango.</p>

        <p><em>Nota: El uso de la señal de telecomunicaciones es exclusivo para la persona que lo contrata. Por ningún motivo podrá compartir la señal, de lo contrario se suspenderá el servicio y será demandado por los daños correspondientes a nuestra empresa.</em></p>';

        return \App\Models\Setting::get('contract_terms', $defaultTerms);
    }

    /**
     * Sube por el árbol de padres de una zona hasta encontrar una que tenga branch_id.
     */
    private function resolveBranchFromZone($zone): ?\App\Models\Branch
    {
        if (!$zone)
            return null;

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
