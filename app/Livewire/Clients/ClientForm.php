<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Plan;
use App\Models\ZonePlanPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientForm extends Component
{
    public $name = '';
    public $document_type = null;
    public $document_number = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $latitude = null;
    public $longitude = null;
    public $nro_luz = '';
    public $installation_address = '';
    public $service = '';
    public $notes = '';

    // === ZONAS Y PLANES ===
    public $branch_id = '';
    public $zone_id = '';
    public $plan_id = '';
    public $availableZones = [];
    public $availablePlans = [];
    public $selectedPlanPrice = null;
    public $selectedPlanService = '';

    public $phones = [];

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/'],
            'document_type' => 'nullable|in:dui',
            'document_number' => ['required', 'string', 'max:10', 'regex:/^\d{8}-\d{1}$/', Rule::unique('clients', 'document_number')],
            'email' => 'nullable|email|max:255',
            'phone' => ['required', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'nro_luz' => ['nullable', 'string', 'max:12', 'regex:/^\d{12}$/', Rule::unique('clients', 'nro_luz')],
            'installation_address' => 'required|string',
            'service' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'zone_id' => 'nullable|exists:zones,id',
            'plan_id' => 'nullable|exists:plans,id',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => ['required_with:phones', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount()
    {
        // Recuperar borrador de sesión para el modal
        $draft = session()->get('client_modal_draft', []);
        $this->name = $draft['name'] ?? '';
        $this->document_type = $draft['document_type'] ?? null;
        $this->document_number = $draft['document_number'] ?? '';
        $this->email = $draft['email'] ?? '';
        $this->phone = $draft['phone'] ?? '';
        $this->address = $draft['address'] ?? '';
        $this->latitude = $draft['latitude'] ?? null;
        $this->longitude = $draft['longitude'] ?? null;
        $this->nro_luz = $draft['nro_luz'] ?? '';
        $this->installation_address = $draft['installation_address'] ?? '';
        $this->service = $draft['service'] ?? '';
        $this->branch_id = $draft['branch_id'] ?? '';
        $this->zone_id = $draft['zone_id'] ?? '';
        $this->plan_id = $draft['plan_id'] ?? '';
        $this->notes = $draft['notes'] ?? '';
        $this->phones = $draft['phones'] ?? [];
        if ($this->branch_id) $this->loadZones();
        if ($this->zone_id) $this->loadPlans();
        if ($this->plan_id) $this->loadSelectedPlan();
    }

    public function updated($property)
    {
        session()->put('client_modal_draft', [
            'name' => $this->name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'nro_luz' => $this->nro_luz,
            'installation_address' => $this->installation_address,
            'service' => $this->service,
            'branch_id' => $this->branch_id,
            'zone_id' => $this->zone_id,
            'plan_id' => $this->plan_id,
            'notes' => $this->notes,
            'phones' => $this->phones,
        ]);
    }

    public function addPhone()
    {
        $this->phones[] = ['number' => '', 'type' => 'personal'];
    }

    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    // ========== ZONAS Y PLANES ==========

    public function updatedBranchId($value)
    {
        $this->zone_id = '';
        $this->plan_id = '';
        $this->availablePlans = [];
        $this->selectedPlanPrice = null;
        $this->selectedPlanService = '';
        if ($value) {
            $this->loadZones();
        } else {
            $this->availableZones = [];
        }
    }

    private function loadZones()
    {
        $this->availableZones = Zone::where('branch_id', $this->branch_id)
            ->orderBy('name')
            ->get()
            ->map(fn($z) => ['id' => $z->id, 'name' => $z->name . ' (' . ucfirst($z->level) . ')'])
            ->toArray();
    }

    public function updatedZoneId($value)
    {
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->selectedPlanService = '';
        if ($value) {
            $this->loadPlans();
        } else {
            $this->availablePlans = [];
        }
    }

    private function loadPlans()
    {
        $zone = Zone::find($this->zone_id);
        if (!$zone) {
            $this->availablePlans = [];
            return;
        }
        $prices = ZonePlanPrice::where('zone_id', $zone->id)
            ->with('plan')
            ->get()
            ->sortBy(fn($zp) => $zp->plan->name);
        $this->availablePlans = $prices->map(function ($zp) use ($zone) {
            $effective = $zone->getEffectivePriceForPlan($zp->plan);
            return [
                'id' => $zp->plan->id,
                'name' => $zp->plan->name,
                'speed' => $zp->plan->speed,
                'service_type' => $zp->plan->service_type,
                'base_price' => $zp->plan->base_price,
                'price' => $effective,
            ];
        })->values()->toArray();
    }

    private function loadSelectedPlan()
    {
        $plan = Plan::find($this->plan_id);
        if (!$plan || !$this->zone_id) return;
        $zone = Zone::find($this->zone_id);
        $this->selectedPlanPrice = $zone?->getEffectivePriceForPlan($plan);
        $this->selectedPlanService = $plan->service_type;
    }

    public function updatedPlanId($value)
    {
        $this->selectedPlanPrice = null;
        $this->selectedPlanService = '';
        if ($value) {
            $this->loadSelectedPlan();
            $plan = Plan::find($value);
            if ($plan) {
                $this->service = match ($plan->service_type) {
                    'internet' => 'Solo Internet',
                    'cable' => 'Solo Cable',
                    'internet_cable' => 'Internet + Cable',
                    default => $plan->service_type,
                };
            }
        } else {
            $this->service = '';
        }
    }

    public function save()
    {
        $this->latitude = is_numeric($this->latitude) ? (float) $this->latitude : null;
        $this->longitude = is_numeric($this->longitude) ? (float) $this->longitude : null;

        // Limpiar nombre
        $this->name = trim(preg_replace('/\s+/', ' ', $this->name));

        $errors = [];
        if ($this->latitude !== null) {
            $latStr = (string) $this->latitude;
            if (!preg_match('/^-?\d{1,2}\.\d+$/', $latStr)) {
                $errors[] = 'Latitud: debe tener punto decimal (ej. 13.6929).';
            } elseif ($this->latitude < -90 || $this->latitude > 90) {
                $errors[] = 'Latitud: valor fuera de rango (-90 a 90).';
            }
        }
        if ($this->longitude !== null) {
            $lonStr = (string) $this->longitude;
            if (!preg_match('/^-?\d{1,3}\.\d+$/', $lonStr)) {
                $errors[] = 'Longitud: debe tener punto decimal (ej. -89.1825).';
            } elseif ($this->longitude < -180 || $this->longitude > 180) {
                $errors[] = 'Longitud: valor fuera de rango (-180 a 180).';
            }
        }
        if ($this->document_number) {
            if (Client::where('document_number', $this->document_number)->exists()) {
                $errors[] = 'El DUI ingresado ya pertenece a otro cliente.';
            }
        }
        if ($this->nro_luz) {
            if (Client::where('nro_luz', $this->nro_luz)->exists()) {
                $errors[] = 'El NC ingresado ya pertenece a otro cliente.';
            }
        }
        if (!empty($errors)) {
            $this->dispatch('show-toasts', errors: $errors);
            return;
        }

        $this->validate();

        $client = Client::create([
            'name' => $this->name,
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'nro_luz' => $this->nro_luz,
            'installation_address' => $this->installation_address,
            'service' => $this->service,
            'branch_id' => $this->branch_id ?: null,
            'zone_id' => $this->zone_id ?: null,
            'plan_id' => $this->plan_id ?: null,
            'notes' => $this->notes,
        ]);

        DB::transaction(function () use ($client) {
            foreach ($this->phones as $phone) {
                if (!empty($phone['number'])) {
                    $client->phones()->create([
                        'number' => $phone['number'],
                        'type' => $phone['type'] ?? 'personal',
                    ]);
                }
            }
        });

        session()->forget('client_modal_draft');
        $this->dispatch(
            'clientCreated',
            id: $client->id,
            name: $client->name,
            phone: $client->phone
        );
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('livewire.clients.client-form', compact('branches'));
    }
}