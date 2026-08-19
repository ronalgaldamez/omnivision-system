<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\InstallFeeRule;
use App\Models\Zone;

class InstallFeeManager extends Component
{
    public $rules = [];
    public $showModal = false;
    public $editingId = null;
    public $zone_id = null;
    public $service_type = 'internet';
    public $covered_meters = 150;
    public $fee = 25;
    public $excess_per_50m = 5;
    public $is_active = true;
    public $zoneSearch = '';
    public $ruleExpandedZones = [];

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    public function mount()
    {
        $this->loadRules();
    }

    public function loadRules()
    {
        $this->rules = InstallFeeRule::with('zone')->orderBy('service_type')->get();
    }

    public function openZoneModal()
    {
        $this->zoneSearch = '';
        $this->ruleExpandedZones = [];
        $this->showZoneModal = true;
    }

    public function closeZoneModal()
    {
        $this->showZoneModal = false;
        $this->zoneSearch = '';
    }

    public function updatedZoneSearch()
    {
        // nada adicional, se filtra en la vista
    }

    public function ruleToggleExpand($zoneId)
    {
        if (in_array($zoneId, $this->ruleExpandedZones)) {
            $this->ruleExpandedZones = array_values(array_diff($this->ruleExpandedZones, [$zoneId]));
        } else {
            $this->ruleExpandedZones[] = $zoneId;
        }
    }

    public function selectZone($id)
    {
        $this->zone_id = (int) $id;
        $this->closeZoneModal();
    }

    public function clearZone()
    {
        $this->zone_id = null;
        $this->zoneSearch = '';
    }

    /**
     * Devuelve la cadena de ancestros de una zona (padres hasta la raíz).
     */
    public function zoneAncestry($zoneId): array
    {
        $zone = Zone::with('parent')->find($zoneId);
        if (!$zone) {
            return [];
        }

        $ancestry = [];
        $current = $zone;
        while ($current && $current->parent) {
            $ancestry[] = ['id' => $current->parent->id, 'name' => $current->parent->name];
            $current = $current->parent;
        }

        return array_reverse($ancestry);
    }

    public function openModal($id = null)
    {
        if ($id) {
            $rule = InstallFeeRule::find($id);
            $this->editingId = $rule->id;
            $this->zone_id = $rule->zone_id;
            $this->service_type = $rule->service_type;
            $this->covered_meters = $rule->covered_meters;
            $this->fee = $rule->fee;
            $this->excess_per_50m = $rule->excess_per_50m;
            $this->is_active = $rule->is_active;
        } else {
            $this->reset(['editingId', 'zone_id']);
            $this->service_type = 'internet';
            $this->covered_meters = 150;
            $this->fee = 25;
            $this->excess_per_50m = 5;
            $this->is_active = true;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate([
            'service_type' => 'required|in:internet,cable,combo',
            'covered_meters' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
            'excess_per_50m' => 'required|numeric|min:0',
        ]);

        $data = [
            'zone_id' => $this->zone_id ?: null,
            'service_type' => $this->service_type,
            'covered_meters' => $this->covered_meters,
            'fee' => $this->fee,
            'excess_per_50m' => $this->excess_per_50m,
            'is_active' => $this->is_active,
        ];

        // Evitar duplicados: misma zona + mismo servicio
        $duplicate = InstallFeeRule::where('service_type', $this->service_type)
            ->where('zone_id', $this->zone_id ?: null)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($duplicate) {
            $zonaLabel = $this->zone_id ? \App\Models\Zone::find($this->zone_id)?->name : 'Global';
            $this->dispatch('show-toast', type: 'error', message: "Ya existe una tarifa de {$this->service_type} para la zona '{$zonaLabel}'. Editá la existente en vez de crear una nueva.");
            return;
        }

        if ($this->editingId) {
            InstallFeeRule::find($this->editingId)->update($data);
        } else {
            InstallFeeRule::create($data);
        }

        $this->closeModal();
        $this->loadRules();
        $this->dispatch('show-toast', type: 'success', message: 'Tarifa de instalación guardada.');
    }

    public function toggleActive($id)
    {
        $rule = InstallFeeRule::find($id);
        $rule->update(['is_active' => !$rule->is_active]);
        $this->loadRules();
    }

    public function promptDelete($id)
    {
        $this->confirmingAction = 'delete';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar esta tarifa de instalación?';
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete') {
            InstallFeeRule::destroy($this->confirmingId);
            $this->loadRules();
            $this->dispatch('show-toast', type: 'success', message: 'Tarifa eliminada.');
        }
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function render()
    {
        $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
        $rootZones = $allZones->whereNull('parent_id');

        return view('livewire.admin.plans.install-fee-manager', [
            'rootZones' => $rootZones,
            'allZones' => $allZones,
        ]);
    }
}
