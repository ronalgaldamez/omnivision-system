<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Plan;
use App\Models\Zone;
use App\Models\PlanRule;

class PlanRuleManager extends Component
{
    public $planRules;
    public $selectedPlanId = '';
    public $plans;

    // Modal
    public $showModal = false;
    public $editingRuleId = null;
    public $ruleZoneId = null;
    public $ruleTermMonths = 12;
    public $ruleKey = '';
    public $ruleValue = '';
    public $ruleCondition = '';
    public $ruleActive = true;

    protected $rules = [
        'selectedPlanId' => 'required|exists:plans,id',
        'ruleZoneId' => 'nullable|exists:zones,id',
        'ruleTermMonths' => 'required|integer|min:1|max:60',
        'ruleKey' => 'required|string|max:50',
        'ruleValue' => 'nullable',
        'ruleCondition' => 'nullable|string|max:50',
    ];

    public function mount()
    {
        $this->plans = Plan::orderBy('name')->get(['id', 'name', 'service_type']);
    }

    public function updatedSelectedPlanId()
    {
        $this->loadRules();
    }

    public function loadRules()
    {
        if (!$this->selectedPlanId) {
            $this->planRules = collect();
            return;
        }
        $this->planRules = PlanRule::with('zone')
            ->where('plan_id', $this->selectedPlanId)
            ->orderBy('term_months')
            ->orderBy('rule_key')
            ->get();
    }

    public function openRuleModal($ruleId = null)
    {
        if ($ruleId) {
            $rule = PlanRule::find($ruleId);
            $this->editingRuleId = $rule->id;
            $this->ruleZoneId = $rule->zone_id;
            $this->ruleTermMonths = $rule->term_months;
            $this->ruleKey = $rule->rule_key;
            $this->ruleValue = is_string($rule->rule_value)
                ? $rule->rule_value
                : json_encode($rule->rule_value);
            $this->ruleCondition = $rule->condition;
            $this->ruleActive = $rule->is_active;
        } else {
            $this->reset(['editingRuleId', 'ruleZoneId', 'ruleTermMonths', 'ruleKey', 'ruleValue', 'ruleCondition']);
            $this->ruleTermMonths = 12;
            $this->ruleActive = true;
        }
        $this->showModal = true;
    }

    public function saveRule()
    {
        $this->validate();

        $value = $this->ruleValue ? json_decode($this->ruleValue, true) ?? ['value' => $this->ruleValue] : null;

        if ($this->editingRuleId) {
            $rule = PlanRule::find($this->editingRuleId);
            $rule->update([
                'zone_id' => $this->ruleZoneId,
                'term_months' => $this->ruleTermMonths,
                'rule_key' => $this->ruleKey,
                'rule_value' => $value,
                'condition' => $this->ruleCondition,
                'is_active' => $this->ruleActive,
            ]);
        } else {
            PlanRule::create([
                'plan_id' => $this->selectedPlanId,
                'zone_id' => $this->ruleZoneId,
                'term_months' => $this->ruleTermMonths,
                'rule_key' => $this->ruleKey,
                'rule_value' => $value,
                'condition' => $this->ruleCondition,
                'is_active' => $this->ruleActive,
            ]);
        }

        $this->showModal = false;
        $this->loadRules();
        $this->dispatch('show-toast', type: 'success', message: 'Regla guardada.');
    }

    public function deleteRule($ruleId)
    {
        PlanRule::destroy($ruleId);
        $this->loadRules();
        $this->dispatch('show-toast', type: 'info', message: 'Regla eliminada.');
    }

    public function toggleRule($ruleId)
    {
        $rule = PlanRule::find($ruleId);
        $rule->update(['is_active' => !$rule->is_active]);
        $this->loadRules();
    }

    public function ruleKeyOptions(): array
    {
        return [
            'free_installation' => 'Instalación gratuita',
            'double_speed' => 'Doble velocidad',
            'free_distance' => 'Distancia gratuita (m)',
            'price_per_meter' => 'Precio por metro extra ($)',
            'discount_months' => 'Meses de descuento (paga X, recibe Y)',
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

    public function render()
    {
        return view('livewire.admin.plans.plan-rule-manager')
            ->layout('components.layouts.app');
    }
}
