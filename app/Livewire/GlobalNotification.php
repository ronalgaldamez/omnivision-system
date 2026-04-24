<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TechnicianRequest;

class GlobalNotification extends Component
{
    public $lastRequestCount = 0;

    public function mount()
    {
        $this->lastRequestCount = TechnicianRequest::count();
    }

    public function checkNewRequests()
    {
        $currentCount = TechnicianRequest::count();
        if ($currentCount > $this->lastRequestCount) {
            $newCount = $currentCount - $this->lastRequestCount;
            $this->dispatch('showToast', ['type' => 'info', 'message' => "📢 $newCount nueva(s) solicitud(es) de materiales"]);
            $this->lastRequestCount = $currentCount;
        }
    }

    public function render()
    {
        return view('livewire.global-notification');
    }
}