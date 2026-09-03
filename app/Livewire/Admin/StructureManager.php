<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class StructureManager extends Component
{
    public $activeTab = 'companies';

    protected $queryString = ['activeTab'];

    public function mount()
    {
        $path = request()->path();

        if (str_contains($path, 'branches')) {
            $this->activeTab = 'branches';
        } else {
            $this->activeTab = 'companies';
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.structure-manager')
            ->layout('components.layouts.app');
    }
}
