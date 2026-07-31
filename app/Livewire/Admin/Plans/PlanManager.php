<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PlanManager extends Component
{
    public $activeTab = 'zones';
    protected $queryString = ['activeTab'];

    public function mount()
    {
        if (Auth::user()->cannot('manage catalog')) {
            abort(403);
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.plans.plan-manager')
            ->layout('components.layouts.app');
    }
}
