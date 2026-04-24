<?php

use App\Helpers\ModuleHelper;
use Illuminate\Support\Facades\Schema; // ← añade este use

if (! function_exists('module_active')) {
    function module_active(string $moduleName): bool
    {
        // Si la tabla 'settings' aún no existe, usar solo config
        if (! Schema::hasTable('settings')) {
            $modules = config('modules.modules', []);
            return isset($modules[$moduleName]) && $modules[$moduleName] === true;
        }

        // La tabla existe: consultar base de datos
        $setting = \App\Models\Setting::where('key', 'module_' . $moduleName)->first();
        if ($setting) {
            return $setting->value === 'true';
        }

        // Si no hay registro en BD, caer en configuración
        $modules = config('modules.modules', []);
        return isset($modules[$moduleName]) && $modules[$moduleName] === true;
    }
}