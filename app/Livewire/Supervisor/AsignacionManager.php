<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zone;
use App\Models\Asignacion;

class AsignacionManager extends Component
{
    public $showForm = false;
    public $editingId = null;
    public $encargado_id = '';
    public $vehicle_id = '';
    public $zone_id = '';
    public $assigned_at = '';

    public $search = '';
    public $expandedZones = [];
    public $zoneSearch = '';

    public function toggleExpandZone($zoneId)
    {
        if (in_array($zoneId, $this->expandedZones)) {
            $this->expandedZones = array_values(array_diff($this->expandedZones, [$zoneId]));
        } else {
            $this->expandedZones[] = $zoneId;
        }
    }

    public function selectZone($zoneId)
    {
        $this->zone_id = (string) $zoneId;
    }

    public function removeZone()
    {
        $this->zone_id = '';
    }

    public function mount()
    {
        $this->assigned_at = now()->format('Y-m-d');
    }

    protected function rules()
    {
        return [
            'encargado_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehiculos,id',
            'zone_id' => 'nullable|exists:zones,id',
            'assigned_at' => 'required|date',
        ];
    }

    public function openForm($id = null)
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $a = Asignacion::findOrFail($id);
            $this->encargado_id = (string) $a->encargado_id;
            $this->vehicle_id = (string) $a->vehicle_id;
            $this->zone_id = (string) $a->zone_id;
            $this->assigned_at = $a->assigned_at->format('Y-m-d');
        } else {
            $this->reset(['encargado_id', 'vehicle_id', 'zone_id']);
            $this->assigned_at = now()->format('Y-m-d');
        }

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'encargado_id' => $this->encargado_id,
            'vehicle_id' => $this->vehicle_id ?: null,
            'zone_id' => $this->zone_id ?: null,
            'is_active' => true,
            'assigned_at' => $this->assigned_at,
        ];

        if ($this->editingId) {
            Asignacion::findOrFail($this->editingId)->update($data);
        } else {
            $this->validateEncargadoNotActive($this->encargado_id);
            $this->validateVehiculoNotActive($this->vehicle_id);
            Asignacion::create($data);
        }

        $this->dispatch('show-toast', type: 'success', message: $this->editingId ? 'Asignación actualizada.' : 'Asignación creada.');
        $this->showForm = false;
        $this->editingId = null;
    }

    protected function validateEncargadoNotActive($userId)
    {
        $exists = Asignacion::where('is_active', true)
            ->where('encargado_id', $userId)
            ->exists();

        if ($exists) {
            $this->addError('encargado_id', 'Este encargado ya tiene una asignación activa.');
        }
    }

    protected function validateVehiculoNotActive($vehicleId)
    {
        $exists = Asignacion::where('is_active', true)
            ->where('vehicle_id', $vehicleId)
            ->exists();

        if ($exists) {
            $this->addError('vehicle_id', 'Este vehículo ya está asignado a otro encargado.');
        }
    }

    public function deactivate($id)
    {
        $a = Asignacion::findOrFail($id);
        $a->update(['is_active' => false, 'ended_at' => now()]);
        $this->dispatch('show-toast', type: 'success', message: 'Asignación finalizada.');
    }

    public function reactivate($id)
    {
        $a = Asignacion::findOrFail($id);
        $a->update(['is_active' => true, 'ended_at' => null, 'assigned_at' => now()]);
        $this->dispatch('show-toast', type: 'success', message: 'Asignación reactivada.');
    }

    public function assignAuxiliar($asignacionId, $auxiliarId)
    {
        $asignacion = Asignacion::findOrFail($asignacionId);

        if ($auxiliarId && $auxiliarId == $asignacion->encargado_id) {
            $this->dispatch('show-toast', type: 'error', message: 'El auxiliar no puede ser el mismo encargado.');
            return;
        }

        $asignacion->update(['auxiliar_id' => $auxiliarId ?: null]);
        $this->dispatch('show-toast', type: 'success', message: $auxiliarId ? 'Auxiliar asignado.' : 'Auxiliar removido.');
    }

    public function render()
    {
        $asignaciones = Asignacion::with(['encargado', 'auxiliar', 'vehicle', 'zone'])
            ->when($this->search, fn($q) => $q->whereHas('encargado', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('vehicle', fn($v) => $v->where('placa', 'like', "%{$this->search}%")))
            ->orderBy('is_active', 'desc')
            ->orderBy('assigned_at', 'desc')
            ->paginate(15);

        $encargados = User::role('technician')->encargados()->orderBy('name')->get(['id', 'name']);
        $tecnicos = User::role('technician')->orderBy('name')->get(['id', 'name']);
        $vehiculos = Vehiculo::where('estado', 'activo')->orderBy('placa')->get(['id', 'placa', 'marca', 'modelo']);
        $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
        $rootZones = $allZones->whereNull('parent_id');
        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        $selectedZoneName = $this->zone_id ? $allZones->firstWhere('id', $this->zone_id)?->name : null;

        return view('livewire.supervisor.asignacion-manager', compact(
            'asignaciones', 'encargados', 'tecnicos', 'vehiculos', 'allZones', 'rootZones', 'branches', 'selectedZoneName'
        ))->layout('components.layouts.app');
    }
}
