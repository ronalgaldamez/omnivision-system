<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Plan;
use App\Models\ZonePlanPrice;
use Illuminate\Support\Facades\Auth;

class ZoneManager extends Component
{
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

    public $selectedZoneId = null;
    public $zonePrices = [];
    public $showPriceModal = false;
    public $editingPriceId = null;
    public $price_plan_id = '';
    public $price_value = '';

    public $viewingZone = null;
    public $viewingZonePriceHistories = [];

    public $showHistoryModal = false;
    public $historyPlanId = null;
    public $historyZoneId = null;
    public $historyRecords = [];

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    public function mount()
    {
        $this->cleanZonePlanPrices();
        $this->collapsedTypes = ['internet', 'cable'];
    }

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
        if (!$parent)
            return;
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

        $historiesByPlan = collect();
        if ($zoneId && $existing->isNotEmpty()) {
            $planIds = $existing->pluck('plan_id');
            $historiesByPlan = \App\Models\PriceHistory::whereIn('plan_id', $planIds)
                ->where('zone_id', $zoneId)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('plan_id');
        }

        $this->zone_plan_prices = $existing->mapWithKeys(function ($zp) use ($historiesByPlan) {
            $histories = $historiesByPlan->get($zp->plan_id, collect())->take(2);
            return [
                $zp->plan_id => [
                    'plan_name' => $zp->plan->name,
                    'plan_speed' => $zp->plan->speed,
                    'plan_service' => $zp->plan->service_type,
                    'base_price' => (float) $zp->plan->base_price,
                    'value' => $zp->price,
                    'history' => $histories->map(fn($h) => [
                        'old_price' => $h->old_price,
                        'new_price' => $h->new_price,
                    ])->toArray(),
                ]
            ];
        })->toArray();
        $this->plan_search = '';
        $this->showPlanSearchResults = false;
    }

    public function getSearchedPlansProperty()
    {
        if (strlen($this->plan_search) < 1)
            return collect();
        return Plan::where('is_active', true)
            ->where('name', 'like', '%' . $this->plan_search . '%')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    public function getSearchedGroupsProperty()
    {
        if (strlen($this->group_search) < 1)
            return collect();
        return \App\Models\PlanGroup::with('plans')
            ->where('name', 'like', '%' . $this->group_search . '%')
            ->orderBy('name')
            ->take(10)
            ->get();
    }

    public function addGroupToZone($groupId)
    {
        $group = \App\Models\PlanGroup::with('plans')->find($groupId);
        if (!$group)
            return;
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
        if (!$plan)
            return;
        if (isset($this->zone_plan_prices[$planId]))
            return;
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
            $planIds = $this->viewingZone->prices()->pluck('plan_id');
            $allHistories = \App\Models\PriceHistory::whereIn('plan_id', $planIds)
                ->where('zone_id', $this->viewingZone->id)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('plan_id');
            foreach ($planIds as $pid) {
                $this->viewingZonePriceHistories[$pid] = $allHistories->get($pid, collect())->take(2);
            }
        }
    }

    public function closeViewZone()
    {
        $this->viewingZone = null;
        $this->viewingZonePriceHistories = [];
    }

    public function saveZone()
    {
        $isContainer = fn($l) => in_array($l, ['departamento', 'municipio']);

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
            if (!$isContainer($this->zone_level))
                $this->saveInlinePrices($zone->id);
            $this->showZoneModal = false;
            $this->dispatch('show-toast', type: 'success', message: 'Zona actualizada.');
            return;
        }

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

            if ($this->zone_municipio_name) {
                Zone::create([
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

        $this->validate(['zone_name' => 'required|string|max:255']);
        $zone = Zone::create([
            'branch_id' => $this->zone_branch_id,
            'parent_id' => $this->zone_parent_id,
            'name' => $this->zone_name,
            'level' => $this->zone_level,
            'has_internet' => $isContainer($this->zone_level) ? false : $this->zone_has_internet,
            'has_cable' => $isContainer($this->zone_level) ? false : $this->zone_has_cable,
        ]);
        if (!$isContainer($this->zone_level))
            $this->saveInlinePrices($zone->id);
        $this->showZoneModal = false;
        $this->dispatch('show-toast', type: 'success', message: "Sub-zona «{$this->zone_name}» creada.");
    }

    private function saveInlinePrices($zoneId)
    {
        $this->cleanZonePlanPrices();

        $submittedIds = array_keys($this->zone_plan_prices);

        ZonePlanPrice::where('zone_id', $zoneId)->whereNotIn('plan_id', $submittedIds)->delete();

        foreach ($this->zone_plan_prices as $planId => $data) {
            $val = $data['value'] ?? '';
            $existing = ZonePlanPrice::where('zone_id', $zoneId)->where('plan_id', $planId)->first();
            $oldPrice = $existing?->price;
            $newPrice = ($val !== '' && $val !== null) ? $val : null;

            $priceRecord = ZonePlanPrice::updateOrCreate(
                ['zone_id' => $zoneId, 'plan_id' => $planId],
                ['price' => $newPrice]
            );

            if ($oldPrice != $newPrice) {
                \App\Models\PriceHistory::create([
                    'zone_plan_price_id' => $priceRecord->id,
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
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function loadPrices()
    {
        if (!$this->selectedZoneId) {
            $this->zonePrices = [];
            return;
        }
        $zone = Zone::with('parent')->find($this->selectedZoneId);
        $prices = ZonePlanPrice::where('zone_id', $zone->id)
            ->with('plan')
            ->get()
            ->sortBy(fn($zp) => $zp->plan->name);

        $ancestorPriceMap = $this->buildAncestorPriceMap($zone);

        $this->zonePrices = $prices->map(function ($price) use ($zone, $ancestorPriceMap) {
            $plan = $price->plan;
            $effective = $zone->getEffectivePriceForPlan($plan);
            $inheritedFrom = $this->findInheritedFrom($zone, $plan, $ancestorPriceMap);
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

    private function buildAncestorPriceMap(Zone $zone): array
    {
        $ancestorIds = [];
        $current = $zone;
        while ($current->parent) {
            $ancestorIds[] = $current->parent_id;
            $current = $current->parent;
        }
        if (empty($ancestorIds))
            return [];

        return ZonePlanPrice::whereIn('zone_id', $ancestorIds)
            ->get()
            ->groupBy('zone_id')
            ->map(fn($items) => $items->keyBy('plan_id'))
            ->toArray();
    }

    private function findInheritedFrom(Zone $zone, Plan $plan, array $ancestorPriceMap): ?string
    {
        $ancestor = $zone->parent;
        while ($ancestor) {
            $prices = $ancestorPriceMap[$ancestor->id] ?? [];
            $p = $prices[$plan->id] ?? null;
            if ($p && $p->price !== null)
                return $ancestor->name . ' (' . $ancestor->level . ')';
            $ancestor = $ancestor->parent;
        }
        return null;
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

        $priceRecord = ZonePlanPrice::updateOrCreate(
            ['zone_id' => $this->selectedZoneId, 'plan_id' => $this->editingPriceId],
            ['price' => $newPrice]
        );

        if ($oldPrice != $newPrice) {
            \App\Models\PriceHistory::create([
                'zone_plan_price_id' => $priceRecord->id,
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

    public function toggleCollapseType($type)
    {
        if (in_array($type, $this->collapsedTypes)) {
            $this->collapsedTypes = array_values(array_diff($this->collapsedTypes, [$type]));
        } else {
            $this->collapsedTypes[] = $type;
        }
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
        $rootZones = $allZones->whereNull('parent_id');
        $selectedZone = $this->selectedZoneId ? Zone::with('branch')->find($this->selectedZoneId) : null;

        return view('livewire.admin.plans.zone-manager', compact(
            'branches',
            'allZones',
            'rootZones',
            'selectedZone'
        ))->layout('components.layouts.app');
    }
}
