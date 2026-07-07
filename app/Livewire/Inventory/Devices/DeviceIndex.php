<?php

namespace App\Livewire\Inventory\Devices;

use App\Models\Device;
use Livewire\Component;
use Livewire\WithPagination;

class DeviceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $productFilter = '';

    protected $queryString = ['productFilter'];

    public function render()
    {
        $devices = Device::with('product', 'technician', 'purchase', 'deviceStatus')
            ->when($this->search, function ($q) {
                $q->where('mac_address', 'like', '%' . $this->search . '%')
                    ->orWhere('pon_sn', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->productFilter, fn($q) => $q->where('product_id', $this->productFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statuses = \App\Models\DeviceStatus::where('is_active', true)->get();
        return view('livewire.inventory.devices.device-index', compact('devices', 'statuses'))
            ->layout('components.layouts.app');
    }
}
