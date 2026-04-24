<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

class ModuleHelper
{
    /**
     * Verifica si un módulo está activo.
     *
     * @param string $moduleName
     * @return bool
     */
    public static function isActive(string $moduleName): bool
    {
        $modules = Config::get('modules.modules', []);
        return isset($modules[$moduleName]) && $modules[$moduleName] === true;
    }
}
