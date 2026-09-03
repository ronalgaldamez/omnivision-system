<?php

namespace App\Livewire\Admin;

use App\Models\Branch;
use App\Models\Company;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public $activeBranchId;

    public $branches = [];

    public $companies = [];

    public function mount()
    {
        $this->activeBranchId = auth()->user()->activeBranchId() ?? '';
        $this->branches = Branch::with('company')->where('is_active', true)->orderBy('name')->get();
        $this->companies = Company::where('is_active', true)
            ->orderBy('razon_social')
            ->with(['branches' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->get();
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
