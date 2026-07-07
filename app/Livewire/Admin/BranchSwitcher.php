<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public $activeBranchId;

    public $branches = [];

    public function mount()
    {
        $this->activeBranchId = auth()->user()->activeBranchId() ?? '';
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();
    }

    public function switchBranch($branchId)
    {
        $branchId = $branchId ?: null;
        session(['active_branch_id' => $branchId]);
        $this->activeBranchId = $branchId ?? '';

        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.admin.branch-switcher');
    }
}
