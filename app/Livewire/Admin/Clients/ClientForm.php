<?php

namespace App\Livewire\Admin\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\ZonePlanPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientForm extends Component
{
    public $clientId = null;
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
    public $contractHistory = [];

    public $phones = [];

    public $confirmingSave = false;
    public $confirmingClear = false;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/'],
            'document_type' => 'nullable|in:dui',
            'document_number' => ['required', 'string', 'max:10', 'regex:/^\d{8}-\d{1}$/', Rule::unique('clients', 'document_number')->ignore($this->clientId)],
            'email' => 'nullable|email|max:255',
            'phone' => ['required', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'nro_luz' => ['nullable', 'string', 'max:12', 'regex:/^\d{12}$/', Rule::unique('clients', 'nro_luz')->ignore($this->clientId)],
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

    public function mount($id = null)
    {
        if ($id) {
            $this->clientId = $id;
            $client = Client::with('phones')->findOrFail($id);
            $this->name = $client->name;
            $this->document_type = $client->document_type;
            $this->document_number = $client->document_number;
            $this->email = $client->email;
            $this->phone = $client->phone;
            $this->address = $client->address;
            $this->latitude = $client->latitude;
            $this->longitude = $client->longitude;
            $this->nro_luz = $client->nro_luz;
            $this->installation_address = $client->installation_address;
            $this->service = $client->service;
            $this->notes = $client->notes;
            $this->branch_id = $client->branch_id;
            $this->zone_id = $client->zone_id;
            $this->plan_id = $client->plan_id;
            $this->phones = $client->phones->toArray();
            if ($this->branch_id) $this->loadZones();
            if ($this->zone_id) $this->loadPlans();
            if ($this->plan_id) $this->loadSelectedPlan();

            // Contract history
            $this->contractHistory = Ticket::where('client_id', $this->clientId)
                ->whereNotNull('plan_id')
                ->with(['plan', 'zone'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($t) {
                    $price = $t->zone && $t->plan ? $t->zone->getEffectivePriceForPlan($t->plan) : null;
                    return [
                        'plan_name' => $t->plan?->name ?? '—',
                        'zone_name' => $t->zone?->name ?? '—',
                        'price' => $price,
                        'date' => $t->created_at->format('d/m/Y'),
                        'status' => $t->status,
                    ];
                })->toArray();
        } else {
            // Recuperar borrador de sesión
            $draft = session()->get('client_form_draft', []);
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
            $this->phones = $draft['phones'] ?? [['number' => '', 'type' => 'personal']];
            if ($this->branch_id) $this->loadZones();
            if ($this->zone_id) $this->loadPlans();
            if ($this->plan_id) $this->loadSelectedPlan();
        }
    }

    public function updated($property)
    {
        if (!$this->clientId) {
            session()->put('client_form_draft', [
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
                'has_override' => $zp->price !== null,
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

    public function addPhone()
    {
        $this->phones[] = ['number' => '', 'type' => 'personal'];
    }

    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    // ========== SAVE ==========

    public function promptSave()
    {
        $this->name = trim(preg_replace('/\s+/', ' ', $this->name));

        $errors = [];
        if ($this->document_number) {
            $query = Client::where('document_number', $this->document_number);
            if ($this->clientId) {
                $query->where('id', '!=', $this->clientId);
            }
            if ($query->exists()) {
                $errors[] = 'El DUI ingresado ya pertenece a otro cliente.';
            }
        }
        if ($this->nro_luz) {
            $query = Client::where('nro_luz', $this->nro_luz);
            if ($this->clientId) {
                $query->where('id', '!=', $this->clientId);
            }
            if ($query->exists()) {
                $errors[] = 'El NC ingresado ya pertenece a otro cliente.';
            }
        }
        if (!empty($errors)) {
            $this->dispatch('show-toasts', errors: $errors);
            return;
        }

        $this->validate();
        $this->confirmingSave = true;
    }

    public function executeSave()
    {
        $this->confirmingSave = false;

        DB::transaction(function () {
            if ($this->clientId) {
                $client = Client::findOrFail($this->clientId);
                if (!auth()->user()->can('edit clients')) {
                    abort(403);
                }
                $client->update([
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
            } else {
                if (!auth()->user()->can('create clients')) {
                    abort(403);
                }
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
            }

            $client->phones()->delete();
            foreach ($this->phones as $phone) {
                if (!empty($phone['number'])) {
                    $client->phones()->create([
                        'number' => $phone['number'],
                        'type' => $phone['type'] ?? 'personal',
                    ]);
                }
            }
        });

        session()->forget('client_form_draft');
        session()->flash('message', $this->clientId ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');
        return redirect()->route('admin.clients.index');
    }

    public function cancelSave()
    {
        $this->confirmingSave = false;
    }

    // ========== CLEAR ==========

    public function promptClear()
    {
        $this->confirmingClear = true;
    }

    public function executeClear()
    {
        $this->confirmingClear = false;
        $this->name = '';
        $this->document_type = null;
        $this->document_number = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->nro_luz = '';
        $this->installation_address = '';
        $this->service = '';
        $this->branch_id = '';
        $this->zone_id = '';
        $this->plan_id = '';
        $this->availableZones = [];
        $this->availablePlans = [];
        $this->selectedPlanPrice = null;
        $this->selectedPlanService = '';
        $this->contractHistory = [];
        $this->notes = '';
        $this->phones = [];
        session()->forget('client_form_draft');
        $this->dispatch('show-toast', type: 'success', message: 'Campos limpiados.');
    }

    public function cancelClear()
    {
        $this->confirmingClear = false;
    }

    // ========== CANCEL (volver atrás) ==========

    public function promptCancel()
    {
        $this->dispatch('confirm-cancel');
    }

    public function executeCancel()
    {
        session()->forget('client_form_draft');
        return redirect()->route('admin.clients.index');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('livewire.admin.clients.client-form', compact('branches'))->layout('components.layouts.app');
    }
}