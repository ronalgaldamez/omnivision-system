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
    public $departamento_id = '';
    public $municipio_id = '';
    public $distrito_id = '';
    public $availableDepartamentos = [];
    public $availableMunicipios = [];
    public $availableDistritos = [];

    // === SERVICIO CONTRATADO ===
    public $branch_id = '';
    public $plan_id = '';
    public $availablePlans = [];
    public $selectedPlanPrice = null;
    public $selectedPlanOrigin = '';
    public $no_price = false;
    public $service = '';
    public $service_type_id = '';
    public $zone_id = '';

    // Cascada de zona de servicio (reemplaza el combo plano)
    public $svc_departamento = '';
    public $svc_municipio = '';
    public $svc_distrito = '';
    public $svc_subzona = '';
    public $svcAvailableDepartamentos = [];
    public $svcAvailableMunicipios = [];
    public $svcAvailableDistritos = [];
    public $svcAvailableSubzonas = [];

    public $accepts_promotions = false;
    public $documentTypesEnabled = false;
    public $documentTypesList = [];

    public $phones = [];

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\.\'\-,]+$/'],
            'document_type' => 'nullable|string|max:50',
            'document_number' => ['required', 'string', 'max:50'],
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

        if ($this->accepts_promotions) {
            $rules['email'] = 'required|email|max:255';
        }

        return $rules;
    }

    public function mount()
    {
        $this->loadDepartamentos();
        $this->loadSvcDepartamentos();
        $this->documentTypesEnabled = \App\Models\Setting::get('document_types_enabled', 'false') === 'true';
        $this->documentTypesList = $this->loadDocumentTypes();

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
        $this->departamento_id = $draft['departamento_id'] ?? '';
        $this->municipio_id = $draft['municipio_id'] ?? '';
        $this->distrito_id = $draft['distrito_id'] ?? '';
        $this->branch_id = $draft['branch_id'] ?? '';
        $this->plan_id = $draft['plan_id'] ?? '';
        $this->no_price = $draft['no_price'] ?? false;
        $this->notes = $draft['notes'] ?? '';
        $this->phones = $draft['phones'] ?? [];

        // Restore service zone cascade from draft
        $this->svc_departamento = $draft['svc_departamento'] ?? '';
        $this->svc_municipio = $draft['svc_municipio'] ?? '';
        $this->svc_distrito = $draft['svc_distrito'] ?? '';
        $this->svc_subzona = $draft['svc_subzona'] ?? '';
        $this->zone_id = $draft['zone_id'] ?? '';
        if ($this->svc_departamento) $this->updatedSvcDepartamento($this->svc_departamento);
        if ($this->svc_municipio) $this->updatedSvcMunicipio($this->svc_municipio);
        if ($this->svc_distrito) $this->updatedSvcDistrito($this->svc_distrito);
        if ($this->svc_subzona) $this->updatedSvcSubzona($this->svc_subzona);
        if ($this->zone_id && !$this->svc_departamento) {
            $this->loadPlansForZone($this->zone_id);
        }

        if ($this->departamento_id) $this->updatedDepartamentoId($this->departamento_id);
        if ($this->municipio_id) $this->updatedMunicipioId($this->municipio_id);
    }

    // ========== CASCADA DE ZONAS ==========

    private function loadDepartamentos()
    {
        $query = Zone::whereNull('parent_id')->where('level', 'departamento');
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        $this->availableDepartamentos = $query->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatedDepartamentoId($value)
    {
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->availableMunicipios = [];
        $this->availableDistritos = [];
        $this->distrito = '';
        $this->municipio = '';
        if ($value) {
            $dep = Zone::find($value);
            $this->departamento = $dep?->name ?? '';
            $this->availableMunicipios = Zone::where('parent_id', $value)
                ->where('level', 'municipio')
                ->orderBy('name')->get(['id', 'name'])->toArray();
        } else {
            $this->departamento = '';
        }
    }

    public function updatedMunicipioId($value)
    {
        $this->distrito_id = '';
        $this->availableDistritos = [];
        $this->distrito = '';
        if ($value) {
            $mun = Zone::find($value);
            $this->municipio = $mun?->name ?? '';
            $this->availableDistritos = Zone::where('parent_id', $value)
                ->whereIn('level', ['distrito', 'localidad'])
                ->orderBy('name')->get(['id', 'name'])->toArray();
        } else {
            $this->municipio = '';
        }
    }

    public function updatedDistritoId($value)
    {
        if ($value) {
            $dis = Zone::find($value);
            $this->distrito = $dis?->name ?? '';
        } else {
            $this->distrito = '';
        }
    }

    public function updated($property, $value)
    {
        $draftFields = ['name', 'document_type', 'document_number', 'email', 'phone',
            'address', 'latitude', 'longitude', 'nro_luz', 'installation_address',
            'departamento', 'municipio', 'distrito', 'departamento_id', 'municipio_id', 'distrito_id',
            'branch_id', 'zone_id', 'plan_id', 'no_price', 'notes',
            'svc_departamento', 'svc_municipio', 'svc_distrito', 'svc_subzona'];
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
                'departamento_id' => $this->departamento_id,
                'municipio_id' => $this->municipio_id,
                'distrito_id' => $this->distrito_id,
                'branch_id' => $this->branch_id,
                'zone_id' => $this->zone_id,
                'plan_id' => $this->plan_id,
                'no_price' => $this->no_price,
                'notes' => $this->notes,
                'phones' => $this->phones,
                'svc_departamento' => $this->svc_departamento,
                'svc_municipio' => $this->svc_municipio,
                'svc_distrito' => $this->svc_distrito,
                'svc_subzona' => $this->svc_subzona,
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
        $this->availablePlans = [];
    }

    public function updatedBranchId($value)
    {
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->selectedPlanOrigin = '';
        $this->service = '';
        $this->zone_id = '';
        $this->departamento_id = '';
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->availablePlans = [];
        $this->availableMunicipios = [];
        $this->availableDistritos = [];
        $this->departamento = '';
        $this->municipio = '';
        $this->distrito = '';
        $this->svc_departamento = '';
        $this->svc_municipio = '';
        $this->svc_distrito = '';
        $this->svc_subzona = '';
        $this->svcAvailableDepartamentos = [];
        $this->svcAvailableMunicipios = [];
        $this->svcAvailableDistritos = [];
        $this->svcAvailableSubzonas = [];
        $this->loadSvcDepartamentos();
        $this->loadDepartamentos();
    }

    // ========== CASCADA DE ZONA DE SERVICIO ==========

    private function loadSvcDepartamentos()
    {
        $query = Zone::whereNull('parent_id')->where('level', 'departamento');
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        $this->svcAvailableDepartamentos = $query->orderBy('name')->get(['id', 'name'])->toArray();
    }

    private function resetSvcCascade($from = 'departamento')
    {
        if ($from === 'departamento') {
            $this->svc_municipio = '';
            $this->svcAvailableMunicipios = [];
        }
        if (in_array($from, ['departamento', 'municipio'])) {
            $this->svc_distrito = '';
            $this->svcAvailableDistritos = [];
        }
        if (in_array($from, ['departamento', 'municipio', 'distrito'])) {
            $this->svc_subzona = '';
            $this->svcAvailableSubzonas = [];
        }
        $this->zone_id = '';
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->selectedPlanOrigin = '';
        $this->service = '';
        $this->availablePlans = [];
    }

    public function updatedSvcDepartamento($value)
    {
        $this->resetSvcCascade('departamento');
        if (!$value) return;
        $this->svcAvailableMunicipios = Zone::where('parent_id', $value)
            ->where('level', 'municipio')
            ->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatedSvcMunicipio($value)
    {
        $this->resetSvcCascade('municipio');
        if (!$value) return;
        $this->svcAvailableDistritos = Zone::where('parent_id', $value)
            ->where('level', 'distrito')
            ->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatedSvcDistrito($value)
    {
        $this->resetSvcCascade('distrito');
        if (!$value) return;
        // Check if this distrito has deeper children (cantón, caserío, localidad, etc.)
        $children = Zone::where('parent_id', $value)
            ->whereNotIn('level', ['departamento', 'municipio', 'distrito'])
            ->orderBy('name')->get(['id', 'name']);
        if ($children->isNotEmpty()) {
            $this->svcAvailableSubzonas = $children->toArray();
        } else {
            // No deeper levels — this is the final zone
            $this->loadPlansForZone($value);
        }
    }

    public function updatedSvcSubzona($value)
    {
        if (!$value) {
            $this->zone_id = '';
            $this->availablePlans = [];
            $this->plan_id = '';
            $this->selectedPlanPrice = null;
            $this->selectedPlanOrigin = '';
            $this->service = '';
            return;
        }
        // Check for even deeper levels
        $children = Zone::where('parent_id', $value)
            ->whereNotIn('level', ['departamento', 'municipio', 'distrito'])
            ->orderBy('name')->get(['id', 'name']);
        if ($children->isNotEmpty()) {
            // There's yet another level — we'd need more combos, but for now
            // just use this zone and show a note
            $this->svcAvailableSubzonas = $children->toArray();
        }
        $this->loadPlansForZone($value);
    }

    private function loadPlansForZone($zoneId)
    {
        $this->zone_id = $zoneId;
        $this->plan_id = '';
        $this->selectedPlanPrice = null;
        $this->selectedPlanOrigin = '';
        $this->service = '';
        $this->availablePlans = [];

        if (!$zoneId) return;

        $zone = Zone::find($zoneId);
        if (!$zone) return;

        // Only show plans that have a direct assignment in zone_plan_prices for this zone
        $assignedPlanIds = \App\Models\ZonePlanPrice::where('zone_id', $zoneId)
            ->pluck('plan_id')
            ->unique();

        if ($assignedPlanIds->isEmpty()) return;

        $this->availablePlans = Plan::where('is_active', true)
            ->whereIn('id', $assignedPlanIds)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'speed' => $p->speed,
                'service_type' => $p->service_type,
                'base_price' => (float) $p->base_price,
                'price' => $zone->getEffectivePriceForPlan($p),
            ])
            ->values()
            ->toArray();
    }

    public function updatedServiceTypeId($value)
    {
        $serviceType = \App\Models\ServiceType::find($value);
        $this->service = $serviceType ? $serviceType->name : '';
    }

    private function loadDocumentTypes(): array
    {
        $raw = \App\Models\Setting::get('document_types', 'DUI,NIT,Pasaporte');
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    // ========== PLANES ==========

    private function getPlanEffectivePrice($planId)
    {
        if (!$planId) return null;
        $plan = Plan::find($planId);
        if (!$plan) return null;

        if ($this->zone_id) {
            $zone = Zone::find($this->zone_id);
            if ($zone) return $zone->getEffectivePriceForPlan($plan);
        }
        if ($this->branch_id) {
            $zone = $this->getBranchPricingZone($this->branch_id);
            if ($zone) return $zone->getEffectivePriceForPlan($plan);
        }
        return $plan->base_price;
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
            $this->selectedPlanOrigin = '';
            $this->service = '';
        } elseif ($this->plan_id) {
            $this->selectedPlanPrice = $this->getPlanEffectivePrice($this->plan_id);
        }
    }

    public function updatedPlanId($value)
    {
        $this->selectedPlanPrice = null;
        $this->selectedPlanOrigin = '';
        if ($value) {
            if (!$this->no_price) {
                $this->selectedPlanPrice = $this->getPlanEffectivePrice($value);
                $this->selectedPlanOrigin = $this->findPlanPriceOrigin($this->plan_id);
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

    private function findPlanPriceOrigin($planId)
    {
        if (!$this->zone_id || !$planId) return '';
        $zone = Zone::find($this->zone_id);
        $plan = Plan::find($planId);
        if (!$zone || !$plan) return '';

        // Check if there's an explicit override at this exact zone
        $localPrice = \App\Models\ZonePlanPrice::where('zone_id', $zone->id)
            ->where('plan_id', $plan->id)->first();
        if ($localPrice && $localPrice->price !== null) {
            return 'override';
        }

        // Walk up ancestors to find inheritance source
        $ancestor = $zone->parent;
        while ($ancestor) {
            $p = \App\Models\ZonePlanPrice::where('zone_id', $ancestor->id)
                ->where('plan_id', $plan->id)->first();
            if ($p && $p->price !== null) {
                return 'inherited:' . $ancestor->name . ' (' . $ancestor->level . ')';
            }
            $ancestor = $ancestor->parent;
        }

        // Falls back to base price
        return 'base';
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
            'zone_id' => $this->distrito_id ?: null,
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
        $this->dispatch('clientCreated', id: $client->id, name: $client->name, phone: $client->phone, service_type_id: $this->service_type_id);
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('livewire.clients.client-form', compact('branches'));
    }
}
