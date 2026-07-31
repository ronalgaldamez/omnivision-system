<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class PlanList extends Component
{
    use WithPagination;

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

    public $viewingPlan = null;
    public $viewingPlanHistories = [];

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    protected $rules = [
        'plan_name' => 'required|string|max:255',
        'plan_service_type' => 'required|in:internet,cable,internet_cable',
        'plan_base_price' => 'required|numeric|min:0',
        'plan_speed' => 'nullable|string|max:50',
        'plan_channels' => 'nullable|integer|min:0',
    ];

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

    public function executeConfirmedAction()
    {
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

    public function render()
    {
        $plans = Plan::when($this->planSearch, fn($q) => $q->where('name', 'like', '%' . $this->planSearch . '%'))
            ->when($this->planFilterType, fn($q) => $q->where('service_type', $this->planFilterType))
            ->when($this->planPriceMin !== '', fn($q) => $q->where('base_price', '>=', $this->planPriceMin))
            ->when($this->planPriceMax !== '', fn($q) => $q->where('base_price', '<=', $this->planPriceMax))
            ->orderByRaw('CASE WHEN speed IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CAST(SUBSTRING_INDEX(speed, " ", 1) AS UNSIGNED) ASC')
            ->orderBy('name')
            ->paginate(50);

        return view('livewire.admin.plans.plan-list', compact('plans'))
            ->layout('components.layouts.app');
    }
}
