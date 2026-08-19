<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\Zone;

class CampaignManager extends Component
{
    public $campaigns = [];
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $type = 'free_installation';
    public $service = 'all';
    public $zone_id = null;
    public $zoneSearch = '';
    public $ruleExpandedZones = [];
    public $starts_at = '';
    public $ends_at = '';
    public $is_active = true;

    // Datos de la promo según tipo
    public $cfg_min_pay = '';
    public $cfg_free = '';
    public $cfg_enabled = true;

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    public function mount()
    {
        $this->loadCampaigns();
    }

    public function loadCampaigns()
    {
        $this->campaigns = Campaign::with('zone')->orderByDesc('starts_at')->get();
    }

    public function openModal($id = null)
    {
        if ($id) {
            $c = Campaign::find($id);
            $this->editingId = $c->id;
            $this->name = $c->name;
            $this->type = $c->type;
            $this->service = $c->service ?? 'all';
            $this->zone_id = $c->zone_id;
            $this->starts_at = $c->starts_at?->format('Y-m-d') ?? '';
            $this->ends_at = $c->ends_at?->format('Y-m-d') ?? '';
            $this->is_active = $c->is_active;
            $this->cfg_min_pay = $c->config['min_pay'] ?? '';
            $this->cfg_free = $c->config['free'] ?? '';
            $this->cfg_enabled = $c->config['enabled'] ?? true;
        } else {
            $this->reset(['editingId', 'zone_id']);
            $this->name = '';
            $this->type = 'free_installation';
            $this->service = 'all';
            $this->starts_at = '';
            $this->ends_at = '';
            $this->is_active = true;
            $this->cfg_min_pay = '';
            $this->cfg_free = '';
            $this->cfg_enabled = true;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function ruleToggleExpand($zoneId)
    {
        if (in_array($zoneId, $this->ruleExpandedZones)) {
            $this->ruleExpandedZones = array_values(array_diff($this->ruleExpandedZones, [$zoneId]));
        } else {
            $this->ruleExpandedZones[] = $zoneId;
        }
    }

    public function updatedZoneSearch()
    {
        // se filtra en la vista
    }

    public function updatedType($value)
    {
        // Limpiar config al cambiar el tipo de promoción
        $this->cfg_min_pay = '';
        $this->cfg_free = '';
        $this->cfg_enabled = true;

        // Sugerir servicio según el tipo (mensual gratis=Cable, doble velocidad=Internet)
        $this->service = match ($value) {
            'discount_months' => 'cable',
            'double_speed' => 'internet',
            default => 'all',
        };
    }

    public function selectZone($zoneId)
    {
        $this->zone_id = (int) $zoneId;
    }

    public function clearZone()
    {
        $this->zone_id = null;
        $this->zoneSearch = '';
    }

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

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|string|max:50',
        ]);

        $isContractRule = in_array($this->type, ['discount_months', 'double_speed']);

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'service' => $this->service,
            'zone_id' => $this->zone_id ?: null,
            'config' => $this->buildConfig(),
            'starts_at' => $isContractRule ? null : ($this->starts_at ? $this->starts_at . ' 00:00:00' : null),
            'ends_at' => $isContractRule ? null : ($this->ends_at ? $this->ends_at . ' 23:59:59' : null),
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Campaign::find($this->editingId)->update($data);
        } else {
            Campaign::create($data);
        }

        $this->closeModal();
        $this->loadCampaigns();
        $this->dispatch('show-toast', type: 'success', message: 'Campaña guardada.');
    }

    public function toggleActive($id)
    {
        $c = Campaign::find($id);
        $c->update(['is_active' => !$c->is_active]);
        $this->loadCampaigns();
    }

    /**
     * Construye el JSON de configuración de la promo según el tipo.
     */
    protected function buildConfig(): ?array
    {
        return match ($this->type) {
            'discount_months' => [
                'min_pay' => (int) $this->cfg_min_pay,
                'free' => (int) $this->cfg_free,
            ],
            'double_speed' => [
                'enabled' => $this->cfg_enabled,
            ],
            default => null,
        };
    }

    public function promptDelete($id)
    {
        $this->confirmingAction = 'delete';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar esta campaña?';
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete') {
            Campaign::destroy($this->confirmingId);
            $this->loadCampaigns();
            $this->dispatch('show-toast', type: 'success', message: 'Campaña eliminada.');
        }
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function render()
    {
        $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
        $rootZones = $allZones->whereNull('parent_id');

        return view('livewire.admin.plans.campaign-manager', [
            'rootZones' => $rootZones,
            'allZones' => $allZones,
        ]);
    }
}
