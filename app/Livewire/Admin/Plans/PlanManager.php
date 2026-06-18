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
    public $zone_municipio_name = '';
    public $zone_level = 'departamento';
    public $zone_has_internet = true;
    public $zone_has_cable = true;
    public $expandedZones = [];
    public $zoneActionMenu = null;
    public $zone_plan_prices = [];
    public $plan_search = '';
    public $showPlanSearchResults = false;
    public $group_search = '';
    public $collapsedTypes = [];

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
    public $planFilterType = '';
    public $planPriceMin = '';
    public $planPriceMax = '';

    // ========== HISTORIAL ==========
    public $historySearch = '';
    public $historyDateFrom = '';
    public $historyDateTo = '';

    // ========== GRUPOS ==========
    public $showGroupModal = false;
    public $editingGroupId = null;
    public $group_name = '';
    public $group_description = '';
    public $group_plan_ids = [];
    public $groupPlanFilterType = '';

    // ========== PRECIOS ==========
    public $selectedZoneId = null;
    public $zonePrices = [];
    public $showPriceModal = false;
    public $editingPriceId = null;
    public $price_plan_id = '';
    public $price_value = '';

    // ========== VISUALIZAR ZONA ==========
    public $viewingZone = null;
    public $viewingZonePriceHistories = [];

    // ========== VISUALIZAR PLAN ==========
    public $viewingPlan = null;
    public $viewingPlanHistories = [];

    // ========== HISTORIAL ==========
    public $showHistoryModal = false;
    public $historyPlanId = null;
    public $historyZoneId = null;
    public $historyRecords = [];

    // ========== CONFIRMACIONES ==========
    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    protected function rules()
    {
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
        $this->cleanZonePlanPrices();
        $this->collapsedTypes = ['internet', 'cable'];
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // ========== ZONAS ==========
    public function openZoneModal($id = null)
    {
        $this->resetValidation();
        $this->cleanZonePlanPrices();
        $this->editingZoneId = $id;
        if ($id) {
            $zone = Zone::findOrFail($id);
            $this->zone_branch_id = $zone->branch_id;
            $this->zone_parent_id = $zone->parent_id;
            $this->zone_name = $zone->name;
            $this->zone_municipio_name = '';
            $this->zone_level = $zone->level;
            $this->zone_has_internet = $zone->has_internet;
            $this->zone_has_cable = $zone->has_cable;
            $this->loadZonePlanPrices($zone);
        } else {
            $this->zone_branch_id = '';
            $this->zone_parent_id = '';
            $this->zone_name = '';
            $this->zone_municipio_name = '';
            $this->zone_level = 'departamento';
            $this->zone_has_internet = true;
            $this->zone_has_cable = true;
            $this->loadZonePlanPrices();
        }
        $this->showZoneModal = true;
    }

    public function openSubZoneModal($parentId)
    {
        $this->zoneActionMenu = null;
        $this->resetValidation();
        $this->cleanZonePlanPrices();
        $this->editingZoneId = null;
        $parent = Zone::findOrFail($parentId);
        $this->zone_branch_id = $parent->branch_id;
        $this->zone_parent_id = $parentId;
        $this->zone_name = '';
        $this->zone_municipio_name = '';
        $this->zone_has_internet = $parent->has_internet;
        $this->zone_has_cable = $parent->has_cable;
        $nextLevels = ['departamento' => 'municipio', 'municipio' => 'distrito', 'distrito' => 'cantón', 'cantón' => 'caserío'];
        $this->zone_level = $nextLevels[$parent->level] ?? 'localidad';
        $this->loadZonePlanPrices();
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

    public function loadZonePlanPrices($zone = null)
    {
        $zoneId = $zone?->id;
        $existing = $zoneId ? ZonePlanPrice::where('zone_id', $zoneId)->with('plan')->get() : collect();

        $this->zone_plan_prices = $existing->mapWithKeys(function ($zp) use ($zoneId) {
            $histories = $zoneId ? \App\Models\PriceHistory::where('plan_id', $zp->plan_id)
                ->where('zone_id', $zoneId)
                ->orderByDesc('created_at')
                ->take(2)
                ->get() : collect();
            return [$zp->plan_id => [
                'plan_name' => $zp->plan->name,
                'plan_speed' => $zp->plan->speed,
                'plan_service' => $zp->plan->service_type,
                'base_price' => (float) $zp->plan->base_price,
                'value' => $zp->price,
                'history' => $histories->map(fn($h) => [
                    'old_price' => $h->old_price,
                    'new_price' => $h->new_price,
                ])->toArray(),
            ]];
        })->toArray();
        $this->plan_search = '';
        $this->showPlanSearchResults = false;
    }

    public function getSearchedPlansProperty()
    {
        if (strlen($this->plan_search) < 1) return collect();
        return Plan::where('is_active', true)
            ->where('name', 'like', '%' . $this->plan_search . '%')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    public function getSearchedGroupsProperty()
    {
        if (strlen($this->group_search) < 1) return collect();
        return \App\Models\PlanGroup::with('plans')
            ->where('name', 'like', '%' . $this->group_search . '%')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    public function addGroupToZone($groupId)
    {
        $group = \App\Models\PlanGroup::with('plans')->find($groupId);
        if (!$group) return;
        foreach ($group->plans as $plan) {
            if (!isset($this->zone_plan_prices[$plan->id])) {
                $this->zone_plan_prices[$plan->id] = [
                    'plan_name' => $plan->name,
                    'plan_speed' => $plan->speed,
                    'plan_service' => $plan->service_type,
                    'base_price' => (float) $plan->base_price,
                    'value' => null,
                ];
            }
        }
        $this->group_search = '';
    }

    public function addPlanToZone($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) return;
        if (isset($this->zone_plan_prices[$planId])) return; // already added
        $this->zone_plan_prices[$planId] = [
            'plan_name' => $plan->name,
            'plan_speed' => $plan->speed,
            'plan_service' => $plan->service_type,
            'base_price' => (float) $plan->base_price,
            'value' => null,
        ];
        $this->plan_search = '';
        $this->showPlanSearchResults = false;
    }

    public function removePlanFromZone($planId)
    {
        unset($this->zone_plan_prices[$planId]);
    }

    private function cleanZonePlanPrices()
    {
        $this->zone_plan_prices = collect($this->zone_plan_prices)
            ->filter(fn($d, $id) => is_numeric($id) && $id > 0 && is_array($d) && isset($d['plan_service']))
            ->toArray();
    }

    public function updatedZoneLevel($value)
    {
        if (in_array($value, ['departamento', 'municipio'])) {
            $this->zone_has_internet = false;
            $this->zone_has_cable = false;
            $this->zone_plan_prices = [];
        }
    }

    public function toggleZoneMenu($zoneId)
    {
        $this->zoneActionMenu = $this->zoneActionMenu === $zoneId ? null : $zoneId;
    }

    public function viewZone($id)
    {
        $this->viewingZone = Zone::with('branch', 'parent', 'children')->find($id);
        $this->viewingZonePriceHistories = [];
        if ($this->viewingZone) {
            $plans = $this->viewingZone->prices()->pluck('plan_id');
            foreach ($plans as $pid) {
                $histories = \App\Models\PriceHistory::where('plan_id', $pid)
                    ->where('zone_id', $this->viewingZone->id)
                    ->orderByDesc('created_at')
                    ->take(2)
                    ->get();
                $this->viewingZonePriceHistories[$pid] = $histories;
            }
        }
    }

    public function closeViewZone()
    {
        $this->viewingZone = null;
        $this->viewingZonePriceHistories = [];
    }

    public function viewPlan($id)
    {
        $this->viewingPlan = Plan::find($id);
        $this->viewingPlanHistories = collect();
        if ($this->viewingPlan) {
            $this->viewingPlanHistories = \App\Models\PriceHistory::where('plan_id', $id)
                ->whereNull('zone_id')
                ->with('user')
                ->orderByDesc('created_at')
                ->get();
        }
    }

    public function closeViewPlan()
    {
        $this->viewingPlan = null;
        $this->viewingPlanHistories = collect();
    }

    public function saveZone()
    {
        $isContainer = fn($l) => in_array($l, ['departamento', 'municipio']);

        // ——— Editar zona existente ———
        if ($this->editingZoneId) {
            $this->validate([
                'zone_name' => 'required|string|max:255',
                'zone_level' => 'required|string|max:50',
            ]);
            $zone = Zone::updateOrCreate(['id' => $this->editingZoneId], [
                'name' => $this->zone_name,
                'level' => $this->zone_level,
                'has_internet' => $isContainer($this->zone_level) ? false : $this->zone_has_internet,
                'has_cable' => $isContainer($this->zone_level) ? false : $this->zone_has_cable,
            ]);
            if (!$isContainer($this->zone_level)) $this->saveInlinePrices($zone->id);
            $this->showZoneModal = false;
            $this->dispatch('show-toast', type: 'success', message: 'Zona actualizada.');
            return;
        }

        // ——— Nueva Zona Raíz: crea Departamento (+ opcional Municipio) ———
        if (!$this->zone_parent_id) {
            $this->validate([
                'zone_name' => 'required|string|max:255',
                'zone_branch_id' => 'required|exists:branches,id',
            ]);

            $depto = Zone::create([
                'branch_id' => $this->zone_branch_id,
                'parent_id' => null,
                'name' => $this->zone_name,
                'level' => 'departamento',
                'has_internet' => false,
                'has_cable' => false,
            ]);

            $municipio = null;
            if ($this->zone_municipio_name) {
                $municipio = Zone::create([
                    'branch_id' => $this->zone_branch_id,
                    'parent_id' => $depto->id,
                    'name' => $this->zone_municipio_name,
                    'level' => 'municipio',
                    'has_internet' => false,
                    'has_cable' => false,
                ]);
            }

            $this->showZoneModal = false;
            $this->dispatch('show-toast', type: 'success', message: $this->zone_municipio_name
                ? "Departamento «{$this->zone_name}» y municipio «{$this->zone_municipio_name}» creados."
                : "Departamento «{$this->zone_name}» creado.");
            return;
        }

        // ——— Sub-zona (vía +) ———
        $this->validate(['zone_name' => 'required|string|max:255']);
        $zone = Zone::create([
            'branch_id' => $this->zone_branch_id,
            'parent_id' => $this->zone_parent_id,
            'name' => $this->zone_name,
            'level' => $this->zone_level,
            'has_internet' => $isContainer($this->zone_level) ? false : $this->zone_has_internet,
            'has_cable' => $isContainer($this->zone_level) ? false : $this->zone_has_cable,
        ]);
        if (!$isContainer($this->zone_level)) $this->saveInlinePrices($zone->id);
        $this->showZoneModal = false;
        $this->dispatch('show-toast', type: 'success', message: "Sub-zona «{$this->zone_name}» creada.");
    }

    private function saveInlinePrices($zoneId)
    {
        $this->cleanZonePlanPrices();

        $submittedIds = array_keys($this->zone_plan_prices);

        // Eliminar los que ya no están en la lista
        ZonePlanPrice::where('zone_id', $zoneId)->whereNotIn('plan_id', $submittedIds)->delete();

        foreach ($this->zone_plan_prices as $planId => $data) {
            $val = $data['value'] ?? '';
            $existing = ZonePlanPrice::where('zone_id', $zoneId)->where('plan_id', $planId)->first();
            $oldPrice = $existing?->price;
            $newPrice = ($val !== '' && $val !== null) ? $val : null;

            ZonePlanPrice::updateOrCreate(
                ['zone_id' => $zoneId, 'plan_id' => $planId],
                ['price' => $newPrice]
            );

            if ($oldPrice != $newPrice) {
                \App\Models\PriceHistory::create([
                    'zone_plan_price_id' => $existing?->id,
                    'plan_id' => $planId,
                    'zone_id' => $zoneId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'user_id' => Auth::id(),
                ]);
            }
        }
        if ($this->selectedZoneId == $zoneId) {
            $this->loadPrices();
        }
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
        if ($this->confirmingAction === 'delete_group') {
            \App\Models\PlanGroup::find($this->confirmingId)?->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Grupo eliminado.');
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

        $speed = $this->plan_speed ? trim($this->plan_speed) : null;
        if ($speed && !str_contains(strtolower($speed), 'mbps') && !str_contains(strtolower($speed), 'gbps')) {
            $speed .= ' Mbps';
        }

        if ($this->editingPlanId) {
            $old = Plan::find($this->editingPlanId);
            $oldPrice = $old?->base_price;
        } else {
            $oldPrice = null;
        }

        Plan::updateOrCreate(['id' => $this->editingPlanId], [
            'name' => $this->plan_name,
            'description' => $this->plan_description,
            'service_type' => $this->plan_service_type,
            'base_price' => $this->plan_base_price,
            'speed' => $speed,
            'channels' => $this->plan_channels ?: null,
        ]);

        if ($this->editingPlanId && $oldPrice != $this->plan_base_price) {
            \App\Models\PriceHistory::create([
                'zone_plan_price_id' => null,
                'plan_id' => $this->editingPlanId,
                'zone_id' => null,
                'old_price' => $oldPrice,
                'new_price' => $this->plan_base_price,
                'user_id' => Auth::id(),
            ]);
        }

        $this->showPlanModal = false;
        $this->dispatch('show-toast', type: 'success', message: $this->editingPlanId ? 'Plan actualizado.' : 'Plan creado.');
    }

    // ========== GRUPOS ==========
    public function openGroupModal($id = null)
    {
        $this->resetValidation();
        $this->editingGroupId = $id;
        if ($id) {
            $group = \App\Models\PlanGroup::with('plans')->findOrFail($id);
            $this->group_name = $group->name;
            $this->group_description = $group->description;
            $this->group_plan_ids = $group->plans->pluck('id')->toArray();
        } else {
            $this->group_name = '';
            $this->group_description = '';
            $this->group_plan_ids = [];
        }
        $this->showGroupModal = true;
    }

    public function saveGroup()
    {
        $this->validate([
            'group_name' => 'required|string|max:255',
            'group_plan_ids' => 'required|array|min:1',
        ]);

        $group = \App\Models\PlanGroup::updateOrCreate(
            ['id' => $this->editingGroupId],
            ['name' => $this->group_name, 'description' => $this->group_description]
        );
        $group->plans()->sync($this->group_plan_ids);
        $this->showGroupModal = false;
        $this->dispatch('show-toast', type: 'success', message: $this->editingGroupId ? 'Grupo actualizado.' : 'Grupo creado.');
    }

    public function toggleCollapseType($type)
    {
        if (in_array($type, $this->collapsedTypes)) {
            $this->collapsedTypes = array_values(array_diff($this->collapsedTypes, [$type]));
        } else {
            $this->collapsedTypes[] = $type;
        }
    }

    public function confirmDeleteGroup($id)
    {
        $this->confirmingAction = 'delete_group';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar este grupo?';
    }

    public function toggleAllFilteredPlans($select)
    {
        $allPlans = Plan::where('is_active', true)->orderBy('name')->get();
        $filteredIds = $this->groupPlanFilterType
            ? $allPlans->where('service_type', $this->groupPlanFilterType)->pluck('id')->toArray()
            : $allPlans->pluck('id')->toArray();

        if ($select) {
            $this->group_plan_ids = array_values(array_unique(array_merge($this->group_plan_ids ?? [], $filteredIds)));
        } else {
            $this->group_plan_ids = array_values(array_diff($this->group_plan_ids ?? [], $filteredIds));
        }
    }

    public function togglePlanActive($id)
    {
        $plan = Plan::find($id);
        if (!$plan) return;
        $plan->update(['is_active' => !$plan->is_active]);
        $this->dispatch('show-toast', type: 'success', message: $plan->is_active ? 'Plan activado.' : 'Plan desactivado.');
    }

    public function promptDeletePlan($id)
    {
        $this->confirmingAction = 'delete_plan';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar este plan permanentemente?';
    }

    // ========== PRECIOS ==========
    public function loadPrices()
    {
        if (!$this->selectedZoneId) {
            $this->zonePrices = [];
            return;
        }
        $zone = Zone::find($this->selectedZoneId);
        $prices = ZonePlanPrice::where('zone_id', $zone->id)
            ->with('plan')
            ->get()
            ->sortBy(fn($zp) => $zp->plan->name);

        $this->zonePrices = $prices->map(function ($price) use ($zone) {
            $plan = $price->plan;
            $effective = $zone->getEffectivePriceForPlan($plan);
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

        $newPrice = ($this->price_value !== '' && $this->price_value !== null) ? $this->price_value : null;
        $existing = ZonePlanPrice::where('zone_id', $this->selectedZoneId)->where('plan_id', $this->editingPriceId)->first();
        $oldPrice = $existing?->price;

        ZonePlanPrice::updateOrCreate(
            ['zone_id' => $this->selectedZoneId, 'plan_id' => $this->editingPriceId],
            ['price' => $newPrice]
        );

        if ($oldPrice != $newPrice) {
            \App\Models\PriceHistory::create([
                'zone_plan_price_id' => $existing?->id,
                'plan_id' => $this->editingPriceId,
                'zone_id' => $this->selectedZoneId,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'user_id' => Auth::id(),
            ]);
        }

        $this->showPriceModal = false;
        $this->loadPrices();
        $this->dispatch('show-toast', type: 'success', message: 'Precio actualizado.');
    }

    public function removePriceOverride($planId)
    {
        $existing = ZonePlanPrice::where('zone_id', $this->selectedZoneId)->where('plan_id', $planId)->first();
        $oldPrice = $existing?->price;

        ZonePlanPrice::where('zone_id', $this->selectedZoneId)->where('plan_id', $planId)->delete();

        \App\Models\PriceHistory::create([
            'zone_plan_price_id' => $existing?->id,
            'plan_id' => $planId,
            'zone_id' => $this->selectedZoneId,
            'old_price' => $oldPrice,
            'new_price' => null,
            'user_id' => Auth::id(),
        ]);

        $this->loadPrices();
        $this->dispatch('show-toast', type: 'success', message: 'Precio restablecido (hereda del padre).');
    }

    public function loadPriceHistory($planId, $zoneId = null)
    {
        $this->historyPlanId = $planId;
        $this->historyZoneId = $zoneId ?? $this->selectedZoneId;
        $query = \App\Models\PriceHistory::where('plan_id', $planId);
        if ($this->historyZoneId) {
            $query->where('zone_id', $this->historyZoneId);
        }
        $this->historyRecords = $query->with('user')->orderByDesc('created_at')->get();
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->historyPlanId = null;
        $this->historyZoneId = null;
        $this->historyRecords = [];
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
                ->when($this->planFilterType, fn($q) => $q->where('service_type', $this->planFilterType))
                ->when($this->planPriceMin !== '', fn($q) => $q->where('base_price', '>=', $this->planPriceMin))
                ->when($this->planPriceMax !== '', fn($q) => $q->where('base_price', '<=', $this->planPriceMax))
                ->orderByRaw('CAST(SUBSTRING_INDEX(COALESCE(speed, name), " ", 1) AS UNSIGNED) ASC')
                ->paginate(50);
        } else {
            $plans = collect();
        }

        $planGroups = \App\Models\PlanGroup::withCount('plans')->orderBy('name')->get();
        $allPlans = Plan::where('is_active', true)->orderBy('name')->get();

        if ($this->activeTab === 'history') {
            $historyQuery = \App\Models\PriceHistory::with('plan', 'zone', 'user');
            if ($this->historySearch) {
                $historyQuery->whereHas('plan', fn($q) => $q->where('name', 'like', '%' . $this->historySearch . '%'))
                    ->orWhereHas('zone', fn($q) => $q->where('name', 'like', '%' . $this->historySearch . '%'));
            }
            if ($this->historyDateFrom) {
                $historyQuery->whereDate('created_at', '>=', $this->historyDateFrom);
            }
            if ($this->historyDateTo) {
                $historyQuery->whereDate('created_at', '<=', $this->historyDateTo);
            }
            $priceHistories = $historyQuery->orderByDesc('created_at')->paginate(50);
        } else {
            $priceHistories = collect();
        }

        $selectedZone = $this->selectedZoneId ? Zone::with('branch')->find($this->selectedZoneId) : null;

        return view('livewire.admin.plans.plan-manager', compact(
            'branches', 'allZones', 'rootZones', 'plans', 'selectedZone', 'planGroups', 'allPlans', 'priceHistories'
        ))->layout('components.layouts.app');
    }
}
