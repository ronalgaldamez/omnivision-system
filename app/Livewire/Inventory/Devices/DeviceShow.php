<?php

namespace App\Livewire\Inventory\Devices;

use App\Models\Device;
use Livewire\Component;

class DeviceShow extends Component
{
    public $device;

    public function mount($id)
    {
        $this->device = Device::with('product', 'purchase', 'technician', 'workOrder', 'deviceStatus')->findOrFail($id);
    }

    public function render()
    {
        $statuses = \App\Models\DeviceStatus::where('is_active', true)->get();
        return view('livewire.inventory.devices.device-show', ['device' => $this->device, 'statuses' => $statuses])
            ->layout('components.layouts.app');
    }
}
