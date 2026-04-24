<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\User;
use App\Models\TechnicianRequest;
use App\Models\TechnicianReturn;

class TechnicianPerformance extends Component
{
    public function render()
    {
        $technicians = User::role('technician')->withCount([
            'technicianRequests as total_requests',
            'technicianRequests as approved_requests' => function ($q) {
                $q->where('status', 'delivered');
            },
            'technicianRequests as rejected_requests' => function ($q) {
                $q->where('status', 'rejected');
            }
        ])->get();

        // Devoluciones por técnico
        foreach ($technicians as $tech) {
            $tech->surplus_returns = TechnicianReturn::where('user_id', $tech->id)->where('type', 'surplus')->count();
            $tech->damage_returns = TechnicianReturn::where('user_id', $tech->id)->where('type', 'damage')->count();
        }

        return view('livewire.reports.technician-performance', compact('technicians'))->layout('components.layouts.app');
    }
}