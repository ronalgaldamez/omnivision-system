<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Zone;
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
    public $notes = '';

    // === DATOS DEL CLIENTE ===
    public $departamento = '';
    public $municipio = '';
    public $distrito = '';

    // === SERVICIO CONTRATADO ===
    public $branch_id = '';
    public $plan_id = '';
    public $availablePlans = [];
    public $selectedPlanPrice = null;
    public $no_price = false;
    public $service = '';

    public $phones = [];

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\.\'\-,]+$/'],
            'document_type' => 'nullable|in:dui',
            'document_number' => ['required', 'string', 'max:10', 'regex:/^\d{8}-\d{1}$/', Rule::unique('clients', 'document_number')],
            'email' => 'nullable|email|max:255',
            'phone' => ['required', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'nro_luz' => ['nullable', 'string', 'max:12', 'regex:/^\d{12}$/', Rule::unique('clients', 'nro_luz')],
            'installation_address' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'plan_id' => 'nullable|exists:plans,id',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => ['nullable', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount()
    {
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
        $this->departamento = $draft['departamento'] ?? '';
        $this->municipio = $draft['municipio'] ?? '';
        $this->distrito = $draft['distrito'] ?? '';
        $this->branch_id = $draft['branch_id'] ?? '';
        $this->plan_id = $draft['plan_id'] ?? '';
        $this->no_price = $draft['no_price'] ?? false;
        $this->notes = $draft['notes'] ?? '';
        $this->phones = $draft['phones'] ?? [];
        if ($this->branch_id) {
            $this->loadBranchPlans($this->branch_id);
        } else {
            $this->loadAllPlans();
        }
    }

    public function updated($property, $value)
    {
        $draftFields = ['name', 'document_type', 'document_number', 'email', 'phone',
            'address', 'latitude', 'longitude', 'nro_luz', 'installation_address',
            'departamento', 'municipio', 'distrito', 'branch_id', 'plan_id',
            'no_price', 'notes'];
        if (in_array($property, $draftFields) || str_starts_with($property, 'phones')) {
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
                'departamento' => $this->departamento,
                'municipio' => $this->municipio,
                'distrito' => $this->distrito,
                'branch_id' => $this->branch_id,
                'plan_id' => $this->plan_id,
                'no_price' => $this->no_price,
                'notes' => $this->notes,
                'phones' => $this->phones,
            ]);
        }
    }

    // ========== SUCURSAL ==========

    private function getBranchPricingZone($branchId)
    {
        if (!$branchId) return null;
        return Zone::where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->first();
    }

    public function loadBranchPlans($branchId)
    {
        $zone = $this->getBranchPricingZone($branchId);
        $this->availablePlans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'speed' => $p->speed,
                'service_type' => $p->service_type,
                'base_price' => $p->base_price,
                'price' => $zone ? $zone->getEffectivePriceForPlan($p) : $p->base_price,
            ])
            ->values()
            ->toArray();
    }

    public function updatedBranchId($value)
    {
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->service = '';
        if ($value) {
            $this->loadBranchPlans($value);
        } else {
            $this->loadAllPlans();
        }
    }

    // ========== PLANES ==========

    private function getPlanEffectivePrice($planId)
    {
        if (!$planId) return null;
        if ($this->branch_id) {
            $zone = $this->getBranchPricingZone($this->branch_id);
            $plan = Plan::find($planId);
            if ($zone && $plan) {
                return $zone->getEffectivePriceForPlan($plan);
            }
        }
        $plan = Plan::find($planId);
        return $plan?->base_price;
    }

    private function loadAllPlans()
    {
        $this->availablePlans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'speed' => $p->speed,
                'service_type' => $p->service_type,
                'base_price' => $p->base_price,
                'price' => $p->base_price,
            ])
            ->values()
            ->toArray();
    }

    public function updatedNoPrice($value)
    {
        if ($value) {
            $this->plan_id = '';
            $this->selectedPlanPrice = null;
            $this->service = '';
        } elseif ($this->plan_id) {
            $this->selectedPlanPrice = $this->getPlanEffectivePrice($this->plan_id);
        }
    }

    public function updatedPlanId($value)
    {
        $this->selectedPlanPrice = null;
        if ($value) {
            if (!$this->no_price) {
                $this->selectedPlanPrice = $this->getPlanEffectivePrice($value);
            }
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

    public function save()
    {
        $this->latitude = is_numeric($this->latitude) ? (float) $this->latitude : null;
        $this->longitude = is_numeric($this->longitude) ? (float) $this->longitude : null;
        $this->name = trim(preg_replace('/\s+/', ' ', $this->name));

        $errors = [];
        if ($this->latitude !== null) {
            $latStr = (string) $this->latitude;
            if (!preg_match('/^-?\d{1,2}\.\d+$/', $latStr)) $errors[] = 'Latitud: debe tener punto decimal (ej. 13.6929).';
            elseif ($this->latitude < -90 || $this->latitude > 90) $errors[] = 'Latitud: valor fuera de rango (-90 a 90).';
        }
        if ($this->longitude !== null) {
            $lonStr = (string) $this->longitude;
            if (!preg_match('/^-?\d{1,3}\.\d+$/', $lonStr)) $errors[] = 'Longitud: debe tener punto decimal (ej. -89.1825).';
            elseif ($this->longitude < -180 || $this->longitude > 180) $errors[] = 'Longitud: valor fuera de rango (-180 a 180).';
        }
        if ($this->document_number && Client::where('document_number', $this->document_number)->exists())
            $errors[] = 'El DUI ingresado ya pertenece a otro cliente.';
        if ($this->nro_luz && Client::where('nro_luz', $this->nro_luz)->exists())
            $errors[] = 'El NC ingresado ya pertenece a otro cliente.';
        if (!empty($errors)) {
            $this->dispatch('show-toast', type: 'error', message: implode(' | ', $errors));
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
            'branch_id' => $this->branch_id ?: null,
            'departamento' => $this->departamento ?: null,
            'municipio' => $this->municipio ?: null,
            'distrito' => $this->distrito ?: null,
            'plan_id' => $this->plan_id ?: null,
            'service' => $this->service,
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
        $this->dispatch('clientCreated', id: $client->id, name: $client->name, phone: $client->phone);
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('livewire.clients.client-form', compact('branches'));
    }
}
