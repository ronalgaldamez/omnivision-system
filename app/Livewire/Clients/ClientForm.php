<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Client;
use App\Models\Zone;

class ClientForm extends Component
{
    public $clientId = null;
    public $name = '';
    public $document_type = null;
    public $document_number = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $notes = '';

    // === DATOS DEL CLIENTE ===
    public $zone_id = '';
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

    public $documentTypesEnabled = false;
    public $documentTypesList = [];

    public $phones = [];

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\.\'\-,]+$/'],
            'document_type' => 'nullable|string|max:50|required_with:document_number',
            'document_number' => ['nullable', 'string', 'max:50'],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:9',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*.number' => 'nullable|string|max:9',
            'phones.*.type' => 'nullable|in:personal,casa,referencia,trabajo,otro',
        ];
    }

    public function mount()
    {
        $this->loadDepartamentos();
        $this->documentTypesEnabled = \App\Models\Setting::get('document_types_enabled', 'false') === 'true';
        $this->documentTypesList = $this->loadDocumentTypes();

        if ($this->clientId) {
            $this->loadClient($this->clientId);
        } else {
            $draft = session()->get('client_modal_draft', []);
            $this->name = $draft['name'] ?? '';
            $this->document_type = $draft['document_type'] ?? null;
            $this->document_number = $draft['document_number'] ?? '';
            $this->email = $draft['email'] ?? '';
            $this->phone = $draft['phone'] ?? '';
            $this->address = $draft['address'] ?? '';
            $this->departamento = $draft['departamento'] ?? '';
            $this->municipio = $draft['municipio'] ?? '';
            $this->distrito = $draft['distrito'] ?? '';
            $this->departamento_id = $draft['departamento_id'] ?? '';
            $this->municipio_id = $draft['municipio_id'] ?? '';
            $this->distrito_id = $draft['distrito_id'] ?? '';
            $this->zone_id = $draft['zone_id'] ?? '';
            $this->branch_id = $draft['branch_id'] ?? '';
            $this->notes = $draft['notes'] ?? '';
            $this->phones = $draft['phones'] ?? [];
        }

        if ($this->departamento_id)
            $this->updatedDepartamentoId($this->departamento_id);
        if ($this->municipio_id)
            $this->updatedMunicipioId($this->municipio_id);

        $this->dispatch('clientFormReady');
    }

    #[On('loadClientData')]
    public function loadClientData($id)
    {
        if ($id) {
            $this->loadClient((int) $id);
        }
    }

    private function loadClient($id)
    {
        $client = Client::with('phones')->findOrFail($id);
        $this->clientId = $client->id;
        $this->name = $client->name;
        $this->document_type = $client->document_type ? strtoupper($client->document_type) : null;
        $this->document_number = $client->document_number;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        $this->departamento = $client->departamento ?? '';
        $this->municipio = $client->municipio ?? '';
        $this->distrito = $client->distrito ?? '';
        $this->departamento_id = '';
        $this->municipio_id = '';
        $this->distrito_id = '';
        $this->zone_id = $client->zone_id ?? '';
        $this->branch_id = $client->branch_id ?? '';
        $this->notes = $client->notes ?? '';
        $this->phones = $client->phones->map(fn($p) => ['number' => $p->number, 'type' => $p->type])->toArray();
    }

    // ========== CASCADA DE ZONAS ==========

    private function loadDepartamentos()
    {
        $this->availableDepartamentos = Zone::whereNull('parent_id')
            ->where('level', 'departamento')
            ->orderBy('name')->get(['id', 'name'])->toArray();
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
            $this->zone_id = (int) $value;

            $branchId = $this->resolveBranchIdFromZone($value);
            if ($branchId) {
                $this->branch_id = $branchId;
            }
        } else {
            $this->distrito = '';
            $this->zone_id = '';
        }
    }

    public function updated($property, $value)
    {
        if ($property === 'name') {
            $this->name = trim(preg_replace('/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\.\'\-,]/', '', $value));
            $this->resetValidation('name');
            $value = $this->name;
        }



        $draftFields = [
            'name',
            'document_type',
            'document_number',
            'email',
            'phone',
            'address',
            'departamento',
            'municipio',
            'distrito',
            'departamento_id',
            'municipio_id',
            'distrito_id',
            'zone_id',
            'branch_id',
            'notes'
        ];
        if (in_array($property, $draftFields) || str_starts_with($property, 'phones')) {
            session()->put('client_modal_draft', [
                'name' => $this->name,
                'document_type' => $this->document_type,
                'document_number' => $this->document_number,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'departamento' => $this->departamento,
                'municipio' => $this->municipio,
                'distrito' => $this->distrito,
                'departamento_id' => $this->departamento_id,
                'municipio_id' => $this->municipio_id,
                'distrito_id' => $this->distrito_id,
                'zone_id' => $this->zone_id,
                'branch_id' => $this->branch_id,
                'notes' => $this->notes,
                'phones' => $this->phones,
            ]);
        }
    }

    private function loadDocumentTypes(): array
    {
        $raw = \App\Models\Setting::get('document_types', 'DUI,NIT,Pasaporte');
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    /**
     * Sube por el árbol de padres de una zona hasta encontrar una que tenga branch_id.
     */
    private function resolveBranchIdFromZone(int $zoneId): ?int
    {
        $zone = Zone::with('parent.parent.parent')->find($zoneId);
        if (!$zone) return null;

        $current = $zone;
        while ($current) {
            if ($current->branch_id) {
                return (int) $current->branch_id;
            }
            $current = $current->parent;
        }

        return null;
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
        $this->name = trim(preg_replace('/\s+/', ' ', $this->name));

        $errors = [];
        if (
            $this->document_number && Client::where('document_number', $this->document_number)
                ->when($this->clientId, fn($q) => $q->where('id', '!=', $this->clientId))
                ->exists()
        ) {
            $errors[] = 'El DUI ingresado ya pertenece a otro cliente.';
        }

        if (!empty($errors)) {
            $this->dispatch('show-toast', type: 'error', message: implode(' | ', $errors));
            return;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'document_type' => $this->document_type ? strtoupper($this->document_type) : null,
            'document_number' => $this->document_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'departamento' => $this->departamento ?: null,
            'municipio' => $this->municipio ?: null,
            'distrito' => $this->distrito ?: null,
            'zone_id' => $this->zone_id ?: null,
            'branch_id' => $this->branch_id ?: null,
            'notes' => $this->notes,
        ];

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $client->update($data);
            $client->phones()->delete();
        } else {
            $client = Client::create($data);
        }

        foreach ($this->phones as $phone) {
            if (!empty($phone['number'])) {
                $client->phones()->create([
                    'number' => $phone['number'],
                    'type' => $phone['type'] ?? 'personal',
                ]);
            }
        }

        session()->forget('client_modal_draft');
        $this->dispatch('clientCreated', id: $client->id, name: $client->name, phone: $client->phone);
    }

    public function render()
    {
        return view('livewire.clients.client-form');
    }
}
