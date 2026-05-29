<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\User;
use App\Models\Requisition;
use App\Models\TechnicianReturn;

class TechnicianPerformance extends Component
{
    public function render()
    {
        $technicians = User::role('technician')->withCount([
            'requisitions as total_requisitions',
            'requisitions as open_requisitions' => function ($q) {
                $q->where('status', 'open');
            },
            'requisitions as closed_requisitions' => function ($q) {
                $q->where('status', 'closed');
            }
        ])->get();

        foreach ($technicians as $tech) {
            $tech->surplus_returns = TechnicianReturn::where('user_id', $tech->id)->where('type', 'surplus')->count();
            $tech->damage_returns = TechnicianReturn::where('user_id', $tech->id)->where('type', 'damage')->count();
        }

        return view('livewire.reports.technician-performance', compact('technicians'))->layout('components.layouts.app');
    }
}