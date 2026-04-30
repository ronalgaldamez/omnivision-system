<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;

class SettingsManager extends Component
{
    public $otRequired = false;
    public $modules = [];
    public $nocPollingInterval = 30; // NUEVO: valor por defecto

    public function mount()
    {
        // Cargar estado de OT obligatoria
        $this->otRequired = Setting::get('ot_required', 'false') === 'true';

        // Cargar estado de los módulos
        $configModules = config('modules.modules', []);
        foreach ($configModules as $key => $default) {
            $dbValue = Setting::get('module_' . $key);
            if ($dbValue !== null) {
                $this->modules[$key] = $dbValue === 'true';
            } else {
                $this->modules[$key] = $default;
            }
        }

        // NUEVO: cargar intervalo de polling del NOC
        $this->nocPollingInterval = (int) Setting::get('noc_polling_interval', 30);
    }

    public function updatedOtRequired()
    {
        Setting::set('ot_required', $this->otRequired ? 'true' : 'false');
        $this->dispatch('show-toast', type: 'success', message: 'Configuración guardada.');
    }

    public function updatedModules($value, $key)
    {
        Setting::set('module_' . $key, $value ? 'true' : 'false');
        $this->dispatch('show-toast', type: 'success', message: "Módulo {$key} " . ($value ? 'activado' : 'desactivado'));
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
    }

    // NUEVO: guardar el intervalo de polling cuando se modifique
    public function updatedNocPollingInterval()
    {
        $this->validate(['nocPollingInterval' => 'required|integer|min:5|max:300']);
        Setting::set('noc_polling_interval', (string) $this->nocPollingInterval);
        $this->dispatch('show-toast', type: 'success', message: 'Intervalo de notificaciones guardado.');
    }

    public function render()
    {
        return view('livewire.admin.settings-manager')->layout('components.layouts.app');
    }
}