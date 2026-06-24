<?php

namespace App\Livewire\Admin\Sla;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SlaGoal;

class SlaGoalIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $goals = SlaGoal::with('serviceType')
            ->where(function ($q) {
                if ($this->search) {
                    $q->where('priority', 'like', '%' . $this->search . '%')
                      ->orWhere('minutes', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                }
            })
            ->orderBy('priority')
            ->orderBy('minutes')
            ->paginate(10);

        return view('livewire.admin.sla.sla-goal-index', compact('goals'))
            ->layout('components.layouts.app');
    }

    public function toggleActive($id)
    {
        $goal = SlaGoal::findOrFail($id);
        $goal->update(['is_active' => !$goal->is_active]);
    }

    public function delete($id)
    {
        $goal = SlaGoal::findOrFail($id);
        $goal->delete();
        session()->flash('message', 'Meta SLA eliminada correctamente.');
    }
}
