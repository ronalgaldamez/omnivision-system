<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Plan;
use App\Models\ZonePlanPrice;
use Illuminate\Support\Facades\Auth;

class PlanManager extends Component
{
    use \Livewire\WithPagination;
    public $activeTab = 'zones';

    // ========== ZONAS ==========
    public $showZoneModal = false;
    public $editingZoneId = null;
    public $zone_branch_id = '';
    public $zone_parent_id = '';
    public $zone_name = '';
    public $zone_level = 'departamento';
    public $zone_has_internet = true;
    public $zone_has_cable = true;
    public $expandedZones = [];

    // ========== PLANES ==========
    public $showPlanModal = false;
    public $editingPlanId = null;
    public $plan_name = '';
    public $plan_description = '';
    public $plan_service_type = 'internet_cable';
    public $plan_base_price = 0;
    public $plan_speed = '';
    public $plan_channels = null;
    public $planSearch = '';

    // ========== PRECIOS ==========
    public $selectedZoneId = null;
    public $zonePrices = [];
    public $showPriceModal = false;
    public $editingPriceId = null;
    public $price_plan_id = '';
    public $price_value = '';

    // ========== CONFIRMACIONES ==========
    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    protected function rules()
    {
        if ($this->activeTab === 'zones') {
            return [
                'zone_name' => 'required|string|max:255',
                'zone_branch_id' => 'required|exists:branches,id',
                'zone_level' => 'required|string|max:50',
                'zone_has_internet' => 'boolean',
                'zone_has_cable' => 'boolean',
                'zone_parent_id' => 'nullable|exists:zones,id',
            ];
        }
        if ($this->activeTab === 'plans') {
            return [
                'plan_name' => 'required|string|max:255',
                'plan_service_type' => 'required|in:internet,cable,internet_cable',
                'plan_base_price' => 'required|numeric|min:0',
                'plan_speed' => 'nullable|string|max:50',
                'plan_channels' => 'nullable|integer|min:0',
            ];
        }
        return [];
    }

    public function mount()
    {
        if (Auth::user()->cannot('manage catalog')) {
            abort(403);
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // ========== ZONAS ==========
    public function openZoneModal($id = null)
    {
        $this->resetValidation();
        $this->editingZoneId = $id;
        if ($id) {
            $zone = Zone::findOrFail($id);
            $this->zone_branch_id = $zone->branch_id;
            $this->zone_parent_id = $zone->parent_id;
            $this->zone_name = $zone->name;
            $this->zone_level = $zone->level;
            $this->zone_has_internet = $zone->has_internet;
            $this->zone_has_cable = $zone->has_cable;
        } else {
            $this->zone_branch_id = '';
            $this->zone_parent_id = '';
            $this->zone_name = '';
            $this->zone_level = 'departamento';
            $this->zone_has_internet = true;
            $this->zone_has_cable = true;
        }
        $this->showZoneModal = true;
    }

    public function openSubZoneModal($parentId)
    {
        $this->resetValidation();
        $this->editingZoneId = null;
        $parent = Zone::findOrFail($parentId);
        $this->zone_branch_id = $parent->branch_id;
        $this->zone_parent_id = $parentId;
        $this->zone_name = '';
        $this->zone_has_internet = $parent->has_internet;
        $this->zone_has_cable = $parent->has_cable;
        $nextLevels = ['departamento' => 'municipio', 'municipio' => 'distrito', 'distrito' => 'cantón', 'cantón' => 'caserío'];
        $this->zone_level = $nextLevels[$parent->level] ?? 'localidad';
        $this->showZoneModal = true;
    }

    public function updatedZoneParentId($value)
    {
        if (empty($value)) {
            $this->zone_level = 'departamento';
            return;
        }
        $parent = Zone::find($value);
        if (!$parent) return;
        $nextLevels = ['departamento' => 'municipio', 'municipio' => 'distrito', 'distrito' => 'cantón', 'cantón' => 'caserío'];
        $this->zone_level = $nextLevels[$parent->level] ?? 'localidad';
    }

    public function zoneAncestry($zoneId): array
    {
        $names = [];
        $current = Zone::find($zoneId);
        while ($current) {
            $names[] = ['name' => $current->name, 'level' => $current->level];
            $current = $current->parent;
        }
        return array_reverse($names);
    }

    public function saveZone()
    {
        $this->validate();

        Zone::updateOrCreate(['id' => $this->editingZoneId], [
            'branch_id' => $this->zone_branch_id,
            'parent_id' => $this->zone_parent_id ?: null,
            'name' => $this->zone_name,
            'level' => $this->zone_level,
            'has_internet' => $this->zone_has_internet,
            'has_cable' => $this->zone_has_cable,
        ]);

        $this->showZoneModal = false;
        $this->dispatch('show-toast', type: 'success', message: $this->editingZoneId ? 'Zona actualizada.' : 'Zona creada.');
    }

    public function toggleExpand($zoneId)
    {
        if (in_array($zoneId, $this->expandedZones)) {
            $this->expandedZones = array_values(array_diff($this->expandedZones, [$zoneId]));
        } else {
            $this->expandedZones[] = $zoneId;
        }
    }

    public function selectZone($zoneId)
    {
        $this->selectedZoneId = $zoneId;
        $this->loadPrices();
    }

    public function promptDeleteZone($id)
    {
        $zone = Zone::find($id);
        if ($zone && $zone->children()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'No se puede eliminar: tiene sub-zonas.');
            return;
        }
        $this->confirmingAction = 'delete_zone';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar esta zona?';
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete_zone') {
            Zone::find($this->confirmingId)?->delete();
            if ($this->selectedZoneId == $this->confirmingId) {
                $this->selectedZoneId = null;
                $this->zonePrices = [];
            }
            $this->dispatch('show-toast', type: 'success', message: 'Zona eliminada.');
        }
        if ($this->confirmingAction === 'delete_plan') {
            Plan::find($this->confirmingId)?->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Plan eliminado.');
        }
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    // ========== PLANES ==========
    public function openPlanModal($id = null)
    {
        $this->resetValidation();
        $this->editingPlanId = $id;
        if ($id) {
            $plan = Plan::findOrFail($id);
            $this->plan_name = $plan->name;
            $this->plan_description = $plan->description;
            $this->plan_service_type = $plan->service_type;
            $this->plan_base_price = $plan->base_price;
            $this->plan_speed = $plan->speed;
            $this->plan_channels = $plan->channels;
        } else {
            $this->plan_name = '';
            $this->plan_description = '';
            $this->plan_service_type = 'internet_cable';
            $this->plan_base_price = 0;
            $this->plan_speed = '';
            $this->plan_channels = null;
        }
        $this->showPlanModal = true;
    }

    public function savePlan()
    {
        $this->validate();

        Plan::updateOrCreate(['id' => $this->editingPlanId], [
            'name' => $this->plan_name,
            'description' => $this->plan_description,
            'service_type' => $this->plan_service_type,
            'base_price' => $this->plan_base_price,
            'speed' => $this->plan_speed ?: null,
            'channels' => $this->plan_channels ?: null,
        ]);

        $this->showPlanModal = false;
        $this->dispatch('show-toast', type: 'success', message: $this->editingPlanId ? 'Plan actualizado.' : 'Plan creado.');
    }

    public function promptDeletePlan($id)
    {
        $this->confirmingAction = 'delete_plan';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar este plan?';
    }

    // ========== PRECIOS ==========
    public function loadPrices()
    {
        if (!$this->selectedZoneId) {
            $this->zonePrices = [];
            return;
        }
        $zone = Zone::find($this->selectedZoneId);
        $plans = Plan::where('is_active', true)
            ->where(function ($q) use ($zone) {
                if ($zone->has_internet && $zone->has_cable) {
                    // todos
                } elseif ($zone->has_internet) {
                    $q->whereIn('service_type', ['internet', 'internet_cable']);
                } elseif ($zone->has_cable) {
                    $q->whereIn('service_type', ['cable', 'internet_cable']);
                } else {
                    $q->whereRaw('1=0');
                }
            })
            ->orderBy('name')->get();
        $this->zonePrices = $plans->map(function ($plan) use ($zone) {
            $price = ZonePlanPrice::firstOrNew([
                'zone_id' => $zone->id,
                'plan_id' => $plan->id,
            ]);
            $effective = $zone->getEffectivePriceForPlan($plan);
            // Find inheritance source
            $inheritedFrom = $this->findInheritedFrom($zone, $plan);
            return [
                'id' => $price->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_service' => $plan->service_type,
                'plan_speed' => $plan->speed,
                'base_price' => (float) $plan->base_price,
                'effective_price' => $effective,
                'override_price' => $price->price,
                'inherited_from' => $inheritedFrom,
            ];
        });
    }

    private function findInheritedFrom(Zone $zone, Plan $plan): ?string
    {
        $price = ZonePlanPrice::where('zone_id', $zone->id)->where('plan_id', $plan->id)->first();
        if ($price && $price->price !== null) return null; // overridden here
        $ancestor = $zone->parent;
        while ($ancestor) {
            $p = ZonePlanPrice::where('zone_id', $ancestor->id)->where('plan_id', $plan->id)->first();
            if ($p && $p->price !== null) return $ancestor->name . ' (' . $ancestor->level . ')';
            $ancestor = $ancestor->parent;
        }
        return null; // base price
    }

    public function editPrice($planId)
    {
        $this->resetValidation();
        $this->editingPriceId = $planId;
        $price = ZonePlanPrice::firstOrNew([
            'zone_id' => $this->selectedZoneId,
            'plan_id' => $planId,
        ]);
        $this->price_value = $price->price;
        $this->showPriceModal = true;
    }

    public function savePrice()
    {
        $this->validate(['price_value' => 'nullable|numeric|min:0']);

        ZonePlanPrice::updateOrCreate(
            ['zone_id' => $this->selectedZoneId, 'plan_id' => $this->editingPriceId],
            ['price' => ($this->price_value !== '' && $this->price_value !== null) ? $this->price_value : null]
        );

        $this->showPriceModal = false;
        $this->loadPrices();
        $this->dispatch('show-toast', type: 'success', message: 'Precio actualizado.');
    }

    public function removePriceOverride($planId)
    {
        ZonePlanPrice::where('zone_id', $this->selectedZoneId)->where('plan_id', $planId)->delete();
        $this->loadPrices();
        $this->dispatch('show-toast', type: 'success', message: 'Precio restablecido (hereda del padre).');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($this->activeTab === 'zones') {
            $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
            $rootZones = $allZones->whereNull('parent_id');
        } else {
            $allZones = collect();
            $rootZones = collect();
        }

        if ($this->activeTab === 'plans') {
            $plans = Plan::when($this->planSearch, fn($q) => $q->where('name', 'like', '%' . $this->planSearch . '%'))
                ->orderBy('name')
                ->paginate(20);
        } else {
            $plans = collect();
        }

        $selectedZone = $this->selectedZoneId ? Zone::with('branch')->find($this->selectedZoneId) : null;

        return view('livewire.admin.plans.plan-manager', compact(
            'branches', 'allZones', 'rootZones', 'plans', 'selectedZone'
        ))->layout('components.layouts.app');
    }
}
