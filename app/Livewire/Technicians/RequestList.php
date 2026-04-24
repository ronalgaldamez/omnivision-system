<?php

namespace App\Livewire\Technicians;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TechnicianRequest;
use Illuminate\Support\Facades\Auth;

class RequestList extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function render()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $query = TechnicianRequest::with('technician', 'products.product', 'workOrder');

        if (!$user->hasRole('admin') && !$user->hasRole('warehouse')) {
            $query->where('technician_id', $user->id);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('request_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('technician', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.technicians.request-list', compact('requests'));
    }


    public function refreshList()
    {
        $this->resetPage();
        $this->render();
        $this->dispatch('showToast', ['type' => 'info', 'message' => 'Nueva solicitud recibida.']);
    }

    public function refreshRequests()
    {
        $this->resetPage();
        $this->render();
        $this->dispatch('showToast', ['type' => 'success', 'message' => '¡Tu solicitud ha sido aprobada!']);
    }
}