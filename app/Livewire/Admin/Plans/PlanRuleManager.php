<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Plan;
use App\Models\Zone;
use App\Models\PlanRule;

class PlanRuleManager extends Component
{
    use WithPagination;

    public $planRules = [];
    public $rulePlanId = '';
    public $rulePlanSearch = '';
    public $rulePlanList = [];
    public $rulePlanListSearch = '';
    public $rulePlanFilterType = '';
    public $showRulePlanModal = false;

    public $showRuleModal = false;
    public $editingRuleId = null;
    public $ruleZoneId = null;
    public $ruleTermMonths = 12;
    public $ruleKey = '';
    public $ruleValue = '';
    public $ruleCondition = '';
    public $ruleActive = true;
    public $ruleExpandedZones = [];
    public $ruleZoneSearch = '';

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    public function mount()
    {
        $this->loadRules();
    }

    public function loadRules()
    {
        if (!$this->rulePlanId) {
            $this->planRules = collect();
            return;
        }
        $rules = PlanRule::with('zone')
            ->where('plan_id', $this->rulePlanId)
            ->orderBy('term_months')
            ->orderBy('rule_key')
            ->get();

        $allZones = Zone::with('children')->get()->keyBy('id');
        $this->planRules = $rules->map(function ($rule) use ($allZones) {
            $rule->inherited_by_count = 0;
            if ($rule->zone_id && isset($allZones[$rule->zone_id])) {
                $countDescendants = function ($zone) use (&$countDescendants) {
                    $total = $zone->children->count();
                    foreach ($zone->children as $child) {
                        $total += $countDescendants($child);
                    }
                    return $total;
                };
                $rule->inherited_by_count = $countDescendants($allZones[$rule->zone_id]);
            }
            return $rule;
        });
    }

    public function updatedRulePlanSearch()
    {
        if (!empty($this->rulePlanSearch)) {
            $selected = Plan::find($this->rulePlanSearch);
            if ($selected) {
                $this->rulePlanSearch = $selected->name . ' (' . str_replace('_', ' ', $selected->service_type) . ')';
            }
        }
    }

    public function selectRulePlan($id)
    {
        $plan = Plan::find($id);
        if ($plan) {
            $this->rulePlanId = $plan->id;
            $this->rulePlanSearch = $plan->name . ' (' . str_replace('_', ' ', $plan->service_type) . ')';
            $this->showRulePlanModal = false;
            $this->loadRules();
        }
    }

    public function clearRulePlan()
    {
        $this->rulePlanId = '';
        $this->rulePlanSearch = '';
        $this->planRules = collect();
    }

    public function openRulePlanModal()
    {
        $this->rulePlanListSearch = '';
        $this->rulePlanList = $this->buildRulePlanQuery()->take(50)->get();
        $this->showRulePlanModal = true;
    }

    public function closeRulePlanModal()
    {
        $this->showRulePlanModal = false;
        $this->rulePlanListSearch = '';
        $this->rulePlanFilterType = '';
        $this->rulePlanList = [];
    }

    public function updatedRulePlanListSearch()
    {
        $this->loadRulePlanList();
    }

    public function updatedRulePlanFilterType()
    {
        $this->loadRulePlanList();
    }

    private function loadRulePlanList()
    {
        $query = $this->buildRulePlanQuery();
        if (strlen($this->rulePlanListSearch) >= 2) {
            $query->where('name', 'like', '%' . $this->rulePlanListSearch . '%');
        }
        $this->rulePlanList = $query->take(50)->get();
    }

    private function buildRulePlanQuery()
    {
        return Plan::where('is_active', true)
            ->withCount('rules')
            ->when($this->rulePlanFilterType, fn($q) => $q->where('service_type', $this->rulePlanFilterType))
            ->orderByRaw('CASE WHEN speed IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(speed, " ", 1) AS UNSIGNED) ASC')
            ->orderBy('name');
    }

    public function ruleToggleExpand($zoneId)
    {
        if (in_array($zoneId, $this->ruleExpandedZones)) {
            $this->ruleExpandedZones = array_values(array_filter($this->ruleExpandedZones, fn($id) => $id !== $zoneId));
        } else {
            $this->ruleExpandedZones[] = $zoneId;
        }
    }

    public function ruleSelectZone($zoneId)
    {
        $this->ruleZoneId = $zoneId;
    }

    public function openRuleModal($ruleId = null)
    {
        if ($ruleId) {
            $rule = PlanRule::find($ruleId);
            $this->editingRuleId = $rule->id;
            $this->ruleZoneId = $rule->zone_id;
            $this->ruleTermMonths = $rule->term_months;
            $this->ruleKey = $rule->rule_key;
            $this->ruleValue = is_string($rule->rule_value) ? $rule->rule_value : json_encode($rule->rule_value);
            $this->ruleCondition = $rule->condition;
            $this->ruleActive = $rule->is_active;
        } else {
            $this->reset(['editingRuleId', 'ruleZoneId', 'ruleTermMonths', 'ruleKey', 'ruleValue', 'ruleCondition']);
            $this->ruleTermMonths = 12;
            $this->ruleActive = true;
        }
        $this->showRuleModal = true;
    }

    public function saveRule()
    {
        $this->validate([
            'rulePlanId' => 'required|exists:plans,id',
            'ruleTermMonths' => 'required|integer|min:1|max:60',
            'ruleKey' => 'required|string|max:50',
        ]);

        $value = $this->ruleValue ? (json_decode($this->ruleValue, true) ?? ['value' => $this->ruleValue]) : null;

        if ($this->editingRuleId) {
            PlanRule::find($this->editingRuleId)->update([
                'zone_id' => $this->ruleZoneId,
                'term_months' => $this->ruleTermMonths,
                'rule_key' => $this->ruleKey,
                'rule_value' => $value,
                'condition' => $this->ruleCondition ?: null,
                'is_active' => $this->ruleActive,
            ]);
        } else {
            PlanRule::updateOrCreate(
                [
                    'plan_id' => $this->rulePlanId,
                    'zone_id' => $this->ruleZoneId,
                    'term_months' => $this->ruleTermMonths,
                    'rule_key' => $this->ruleKey,
                    'condition' => $this->ruleCondition ?: null,
                ],
                [
                    'rule_value' => $value,
                    'is_active' => $this->ruleActive,
                ]
            );
        }

        $this->showRuleModal = false;
        $this->loadRules();
        $this->dispatch('show-toast', type: 'success', message: 'Regla guardada.');
    }

    public function toggleRule($ruleId)
    {
        $rule = PlanRule::find($ruleId);
        $rule->update(['is_active' => !$rule->is_active]);
        $this->loadRules();
    }

    public function deleteRule($ruleId)
    {
        PlanRule::destroy($ruleId);
        $this->loadRules();
        $this->dispatch('show-toast', type: 'info', message: 'Regla eliminada.');
    }

    public function promptDeleteRule($id)
    {
        $this->confirmingAction = 'delete_rule';
        $this->confirmingId = $id;
        $this->confirmMessage = '¿Eliminar esta regla?';
    }

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete_rule') {
            PlanRule::destroy($this->confirmingId);
            $this->loadRules();
            $this->dispatch('show-toast', type: 'info', message: 'Regla eliminada.');
        }
        $this->confirmingAction = null;
        $this->confirmingId = null;
    }

    public function cancelConfirmation()
    {
        $this->confirmingAction = null;
        $this->confirmingId = null;
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

    public function ruleKeyOptions(): array
    {
        return [
            'free_installation' => 'Instalación gratuita',
            'double_speed' => 'Doble velocidad',
            'free_distance' => 'Distancia gratuita (m)',
            'price_per_meter' => 'Precio por metro extra ($)',
            'discount_months' => 'Pago total anual',
            'festive_eligible' => 'Elegible para promos festivas',
        ];
    }

    public function conditionOptions(): array
    {
        return [
            '' => 'Sin condición',
            'new_client' => 'Cliente nuevo',
            'prepaid' => 'Prepago total',
            'distance_override' => 'Excedente por distancia',
            'festive' => 'Solo en festivos',
        ];
    }

    public function formatRuleDisplay($rule): string
    {
        $value = is_array($rule) ? ($rule['rule_value'] ?? $rule->rule_value ?? null) : $rule->rule_value;
        if (!$value) return '—';

        return match ($rule['rule_key'] ?? $rule->rule_key ?? '') {
            'free_distance' => 'Hasta ' . ($value['meters'] ?? '?') . 'm sin costo',
            'price_per_meter' => '$' . ($value['amount'] ?? '?') . ' por metro extra',
            'discount_months' => 'Paga ' . ($value['pay'] ?? '?') . ' meses, recibe ' . ($value['total'] ?? '?'),
            'free_installation', 'double_speed', 'festive_eligible' => 'Activado',
            default => is_array($value) ? json_encode($value) : (string) $value,
        };
    }

    public function render()
    {
        $allZones = Zone::with('branch', 'parent', 'children')->orderBy('name')->get();
        $rootZones = $allZones->whereNull('parent_id');
        $allPlans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.plans.plan-rule-manager', compact(
            'allZones', 'rootZones', 'allPlans'
        ))->layout('components.layouts.app');
    }
}
