<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;

class SettingsManager extends Component
{
    public $otRequired = false;
    public $modules = [];

    public function mount()
    {
        // Cargar estado de OT obligatoria
        $this->otRequired = Setting::get('ot_required', 'false') === 'true';

        // Cargar estado de los módulos (desde la base de datos o desde el archivo por defecto)
        $configModules = config('modules.modules', []);
        foreach ($configModules as $key => $default) {
            $dbValue = Setting::get('module_' . $key);
            if ($dbValue !== null) {
                $this->modules[$key] = $dbValue === 'true';
            } else {
                $this->modules[$key] = $default;
            }
        }
    }

    public function updatedOtRequired()
    {
        Setting::set('ot_required', $this->otRequired ? 'true' : 'false');
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Configuración guardada.']);
    }

    public function updatedModules($value, $key)
    {
        // $key será el nombre del módulo (ej: 'work_orders')
        Setting::set('module_' . $key, $value ? 'true' : 'false');
        $this->dispatch('showToast', ['type' => 'success', 'message' => "Módulo {$key} " . ($value ? 'activado' : 'desactivado')]);
        // Limpiar caché de configuración para que los cambios se reflejen en rutas y menús
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
    }

    public function render()
    {
        return view('livewire.admin.settings-manager')->layout('components.layouts.app');
    }
}