<?php

namespace App\Livewire\Mobile\Technician;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TechnicianRequest;
use Illuminate\Support\Facades\Auth;

class RequestList extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    public function getListeners()
    {
        $userId = auth()->id();
        return [
            "echo:technician.{$userId},approved" => 'refreshRequests'
        ];
    }

    public function refreshRequests()
    {
        $this->resetPage();
        $this->dispatch('showToast', ['type' => 'success', 'message' => '¡Tu solicitud ha sido aprobada!']);
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = TechnicianRequest::with('products.product', 'workOrder')
            ->where('technician_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('request_code', 'like', '%' . $this->search . '%')
                    ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }

        $requests = $query->paginate(10);

        return view('livewire.mobile.technician.request-list', compact('requests'));
    }
}