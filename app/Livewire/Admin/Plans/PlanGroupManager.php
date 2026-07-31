<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Plan;
use App\Models\PlanGroup;

class PlanGroupManager extends Component
{
    public $showGroupModal = false;
    public $editingGroupId = null;
    public $group_name = '';
    public $group_description = '';
    public $group_plan_ids = [];
    public $groupPlanFilterType = '';

    public $confirmingAction = null;
    public $confirmingId = null;
    public $confirmMessage = '';

    protected $rules = [
        'group_name' => 'required|string|max:255',
        'group_plan_ids' => 'required|array|min:1',
    ];

    public function openGroupModal($id = null)
    {
        $this->resetValidation();
        $this->editingGroupId = $id;
        if ($id) {
            $group = PlanGroup::with('plans')->findOrFail($id);
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
        $this->validate();

        $group = PlanGroup::updateOrCreate(
            ['id' => $this->editingGroupId],
            ['name' => $this->group_name, 'description' => $this->group_description]
        );
        $group->plans()->sync($this->group_plan_ids);
        $this->showGroupModal = false;
        $this->dispatch('show-toast', type: 'success', message: $this->editingGroupId ? 'Grupo actualizado.' : 'Grupo creado.');
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

    public function executeConfirmedAction()
    {
        if ($this->confirmingAction === 'delete_group') {
            PlanGroup::find($this->confirmingId)?->delete();
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

    public function render()
    {
        $planGroups = PlanGroup::with('plans')->withCount('plans')->orderBy('name')->get();
        $allPlans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.plans.plan-group-manager', compact('planGroups', 'allPlans'))
            ->layout('components.layouts.app');
    }
}
