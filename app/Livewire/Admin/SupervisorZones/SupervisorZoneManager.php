<?php

namespace App\Livewire\Admin\SupervisorZones;

use Livewire\Component;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupervisorZoneManager extends Component
{
    public $search = '';
    public $showAssignModal = false;
    public $editingZoneId = null;
    public $selectedSupervisors = [];
    public $applyToChildren = false;

    protected function rules()
    {
        return [
            'selectedSupervisors' => 'required|array|min:1',
            'selectedSupervisors.*' => 'exists:users,id',
        ];
    }

    public function editAssignments($zoneId)
    {
        $this->editingZoneId = $zoneId;
        $this->applyToChildren = false;
        $zone = Zone::with('supervisors')->findOrFail($zoneId);
        $this->selectedSupervisors = $zone->supervisors->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->showAssignModal = true;
    }

    public function saveAssignments()
    {
        $this->validate();

        $zone = Zone::findOrFail($this->editingZoneId);
        $zone->supervisors()->sync($this->selectedSupervisors);

        if ($this->applyToChildren) {
            $this->applyToAllDescendants($zone, $this->selectedSupervisors);
        }

        $this->dispatch('show-toast', type: 'success', message: 'Supervisores asignados a la zona.');
        $this->showAssignModal = false;
        $this->editingZoneId = null;
    }

    private function applyToAllDescendants(Zone $zone, array $supervisorIds)
    {
        foreach ($zone->children as $child) {
            $child->supervisors()->sync($supervisorIds);
            $child->load('children');
            $this->applyToAllDescendants($child, $supervisorIds);
        }
    }

    public function removeAssignment($zoneId, $userId)
    {
        DB::table('supervisor_zone')
            ->where('zone_id', $zoneId)
            ->where('user_id', $userId)
            ->delete();

        $this->dispatch('show-toast', type: 'success', message: 'Supervisor removido de la zona.');
    }

    public function render()
    {
        $rootZones = Zone::with([
            'supervisors',
            'children.supervisors',
            'children.children.supervisors',
            'branch',
        ])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $allSupervisors = User::role('field_supervisor')
            ->orderBy('name')
            ->get(['id', 'name']);

        $editingZone = $this->editingZoneId ? Zone::find($this->editingZoneId) : null;

        return view('livewire.admin.supervisor-zones.supervisor-zone-manager', compact(
            'rootZones', 'allSupervisors', 'editingZone'
        ))->layout('components.layouts.app');
    }
}
