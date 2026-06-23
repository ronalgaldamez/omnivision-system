<?php

namespace App\Livewire\Admin\Sla;

use Livewire\Component;
use App\Models\SlaGoal;
use App\Models\ServiceType;

class SlaGoalForm extends Component
{
    public $goalId;
    public $priority = 'P3';
    public $service_type_id = '';
    public $minutes = 60;
    public $is_active = true;
    public $description = '';

    protected $rules = [
        'priority' => 'required|in:P1,P2,P3,P4',
        'service_type_id' => 'nullable|exists:service_types,id',
        'minutes' => 'required|integer|min:1|max:43200',
        'is_active' => 'boolean',
        'description' => 'nullable|string|max:500',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $goal = SlaGoal::findOrFail($id);
            $this->goalId = $goal->id;
            $this->priority = $goal->priority;
            $this->service_type_id = $goal->service_type_id;
            $this->minutes = $goal->minutes;
            $this->is_active = $goal->is_active;
            $this->description = $goal->description;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->goalId) {
            $goal = SlaGoal::findOrFail($this->goalId);
            $goal->update([
                'priority' => $this->priority,
                'service_type_id' => $this->service_type_id ?: null,
                'minutes' => $this->minutes,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Meta SLA actualizada correctamente.');
        } else {
            SlaGoal::create([
                'priority' => $this->priority,
                'service_type_id' => $this->service_type_id ?: null,
                'minutes' => $this->minutes,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Meta SLA creada correctamente.');
        }

        return redirect()->route('admin.sla.goals.index');
    }

    public function render()
    {
        $serviceTypes = ServiceType::orderBy('name')->get();
        return view('livewire.admin.sla.sla-goal-form', compact('serviceTypes'))
            ->layout('components.layouts.app');
    }
}
