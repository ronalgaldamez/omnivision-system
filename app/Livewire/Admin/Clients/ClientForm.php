<?php

namespace App\Livewire\Admin\Clients;

use Livewire\Component;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Zone;
use App\Models\Ticket;
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
    public $notes = '';

    // === DATOS DEL CLIENTE ===
    public $branch_id = '';
    public $departamento = '';
    public $municipio = '';
    public $distrito = '';
    public $departamento_id = '';
    public $municipio_id = '';
    public $distrito_id = '';
    public $availableDepartamentos = [];
    public $availableMunicipios = [];
    public $availableDistritos = [];
    public $assignedSupervisor = '';


    // === SERVICIO CONTRATADO ===
    public $plan_id = '';
    public $availablePlans = [];
    public $selectedPlanPrice = null;
    public $selectedPlanService = '';
    public $no_price = false;
    public $service = '';

    public $phones = [];
    public $contractHistory = [];

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
            'branch_id' => 'nullable|exists:branches,id',
            'plan_id' => 'nullable|exists:plans,id',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => ['required_with:phones', 'string', 'max:9', 'regex:/^\d{4}-\d{4}$/'],
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount($id = null)
    {
        $this->loadDepartamentos();

        if ($id) {
            $this->clientId = $id;
            $client = Client::with('phones', 'zone', 'zone.parent', 'zone.parent.parent')->findOrFail($id);
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
            $this->notes = $client->notes;
            $this->branch_id = $client->branch_id;
            $this->plan_id = $client->plan_id;
            $this->departamento = $client->departamento;
            $this->municipio = $client->municipio;
            $this->distrito = $client->distrito;
            $this->phones = $client->phones->toArray();

            // Restaurar cascada de zonas desde el zone_id del cliente
            if ($client->zone) {
                $zone = $client->zone;
                if ($zone->level === 'distrito' || $zone->level === 'localidad') {
                    $this->distrito_id = $zone->id;
                    if ($zone->parent) {
                        $this->municipio_id = $zone->parent->id;
                        $this->updatedMunicipioId($zone->parent->id);
                        if ($zone->parent->parent) {
                            $this->departamento_id = $zone->parent->parent->id;
                        }
                    }
                } elseif ($zone->level === 'municipio') {
                    $this->municipio_id = $zone->id;
                    if ($zone->parent) {
                        $this->departamento_id = $zone->parent->id;
                        $this->updatedDepartamentoId($zone->parent->id);
                    }
                } elseif ($zone->level === 'departamento') {
                    $this->departamento_id = $zone->id;
                }
            }

            if ($this->plan_id) $this->loadSelectedPlan();

            if ($this->branch_id) {
                $this->loadBranchPlans($this->branch_id);
            } else {
                $this->loadAllPlans();
            }

            $this->contractHistory = Ticket::where('client_id', $this->clientId)
                ->whereNotNull('plan_id')
                ->with('plan')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($t) {
                    return [
                        'plan_name' => $t->plan?->name ?? '—',
                        'price' => $t->plan?->base_price,
                        'date' => $t->created_at->format('d/m/Y'),
                        'status' => $t->status,
                    ];
                })->toArray();
        } else {
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
            $this->branch_id = $draft['branch_id'] ?? '';
            $this->departamento = $draft['departamento'] ?? '';
            $this->municipio = $draft['municipio'] ?? '';
            $this->distrito = $draft['distrito'] ?? '';
            $this->departamento_id = $draft['departamento_id'] ?? '';
            $this->municipio_id = $draft['municipio_id'] ?? '';
            $this->distrito_id = $draft['distrito_id'] ?? '';
            $this->plan_id = $draft['plan_id'] ?? '';
            $this->no_price = $draft['no_price'] ?? false;
            $this->notes = $draft['notes'] ?? '';
            $this->phones = $draft['phones'] ?? [['number' => '', 'type' => 'personal']];
            if ($this->branch_id) {
                $this->loadBranchPlans($this->branch_id);
            } else {
                $this->loadAllPlans();
            }

            // Restaurar cascada desde borrador
            if ($this->departamento_id) {
                $this->updatedDepartamentoId($this->departamento_id);
            }
            if ($this->municipio_id) {
                $this->updatedMunicipioId($this->municipio_id);
            }
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
        $this->departamento_id = '';
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->availableMunicipios = [];
        $this->availableDistritos = [];
        $this->departamento = '';
        $this->municipio = '';
        $this->distrito = '';
        $this->assignedSupervisor = '';
        if ($value) {
            $this->loadBranchPlans($value);
            $this->availableDepartamentos = Zone::whereNull('parent_id')
                ->where('level', 'departamento')
                ->where('branch_id', $value)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } else {
            $this->loadAllPlans();
            $this->availableDepartamentos = Zone::whereNull('parent_id')
                ->where('level', 'departamento')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
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

    private function loadSelectedPlan()
    {
        $plan = Plan::find($this->plan_id);
        if (!$plan) return;
        $this->selectedPlanPrice = $this->getPlanEffectivePrice($this->plan_id);
        $this->selectedPlanService = $plan->service_type;
    }

    public function updatedNoPrice($value)
    {
        if ($value) {
            $this->plan_id = '';
            $this->selectedPlanPrice = null;
            $this->service = '';
        } elseif ($this->plan_id) {
            $this->loadSelectedPlan();
        }
    }

    public function updatedPlanId($value)
    {
        $this->selectedPlanPrice = null;
        if ($value) {
            if (!$this->no_price) $this->loadSelectedPlan();
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

    // ========== CASCADA DE ZONAS ==========

    private function loadDepartamentos()
    {
        $query = Zone::whereNull('parent_id')
            ->where('level', 'departamento');
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }
        $this->availableDepartamentos = $query->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedDepartamentoId($value)
    {
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->availableMunicipios = [];
        $this->availableDistritos = [];
        $this->distrito = '';
        $this->municipio = '';
        $this->assignedSupervisor = '';

        if ($value) {
            $dep = Zone::find($value);
            $this->departamento = $dep?->name ?? '';
            $this->availableMunicipios = Zone::where('parent_id', $value)
                ->where('level', 'municipio')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } else {
            $this->departamento = '';
        }
    }

    public function updatedMunicipioId($value)
    {
        $this->distrito_id = '';
        $this->availableDistritos = [];
        $this->distrito = '';
        $this->assignedSupervisor = '';

        if ($value) {
            $mun = Zone::find($value);
            $this->municipio = $mun?->name ?? '';
            $this->availableDistritos = Zone::where('parent_id', $value)
                ->whereIn('level', ['distrito', 'localidad'])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } else {
            $this->municipio = '';
        }
    }

    public function updatedDistritoId($value)
    {
        $this->assignedSupervisor = '';
        if ($value) {
            $dis = Zone::with('supervisors', 'parent.supervisors', 'parent.parent.supervisors')->find($value);
            $this->distrito = $dis?->name ?? '';
            $supervisorName = $dis?->effectiveSupervisors()->pluck('name')->implode(', ');
            if ($supervisorName) {
                $this->assignedSupervisor = $supervisorName;
            }
        } else {
            $this->distrito = '';
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
                'branch_id' => $this->branch_id,
                'departamento' => $this->departamento,
                'municipio' => $this->municipio,
                'distrito' => $this->distrito,
                'departamento_id' => $this->departamento_id,
                'municipio_id' => $this->municipio_id,
                'distrito_id' => $this->distrito_id,
                'plan_id' => $this->plan_id,
                'no_price' => $this->no_price,
                'notes' => $this->notes,
                'phones' => $this->phones,
            ]);
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
            if ($this->clientId) $query->where('id', '!=', $this->clientId);
            if ($query->exists()) $errors[] = 'El DUI ingresado ya pertenece a otro cliente.';
        }
        if ($this->nro_luz) {
            $query = Client::where('nro_luz', $this->nro_luz);
            if ($this->clientId) $query->where('id', '!=', $this->clientId);
            if ($query->exists()) $errors[] = 'El NC ingresado ya pertenece a otro cliente.';
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
            $data = [
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
                'plan_id' => $this->plan_id ?: null,
                'zone_id' => $this->distrito_id ?: null,
                'departamento' => $this->departamento ?: null,
                'municipio' => $this->municipio ?: null,
                'distrito' => $this->distrito ?: null,
                'service' => $this->service,
                'notes' => $this->notes,
            ];

            if ($this->clientId) {
                $client = Client::findOrFail($this->clientId);
                $client->update($data);
            } else {
                $client = Client::create($data);
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
        $this->branch_id = '';
        $this->departamento = '';
        $this->municipio = '';
        $this->distrito = '';
        $this->departamento_id = '';
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->availableMunicipios = [];
        $this->availableDistritos = [];
        $this->assignedSupervisor = '';
        $this->plan_id = '';
        $this->no_price = false;
        $this->selectedPlanPrice = null;
        $this->service = '';
        $this->contractHistory = [];
        $this->notes = '';
        $this->phones = [];
        $this->loadAllPlans();
        session()->forget('client_form_draft');
        $this->dispatch('show-toast', type: 'success', message: 'Campos limpiados.');
    }

    public function cancelClear()
    {
        $this->confirmingClear = false;
    }

    // ========== CANCEL ==========

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
