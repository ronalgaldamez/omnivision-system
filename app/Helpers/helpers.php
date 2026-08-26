<?php

use App\Helpers\ModuleHelper;
use Illuminate\Support\Facades\Schema; // ← añade este use

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }
        return \App\Models\Setting::get($key, $default);
    }
}

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

if (! function_exists('notification_url')) {
    /**
     * Devuelve la URL de destino de una notificación según su contenido.
     * Devuelve null si la notificación no tiene un destino enlazable.
     */
    function notification_url(array $data): ?string
    {
        if (isset($data['work_order_id'])) {
            $user = auth()->user();
            if ($user && $user->hasRole('technician')) {
                return route('mobile.work-orders.show', $data['work_order_id']);
            }
            return route('work-orders.show', $data['work_order_id']);
        }

        if (isset($data['requisition_id'])) {
            return route('technician.requisitions.show', $data['requisition_id']);
        }

        return null;
    }
}

if (! function_exists('notification_visual')) {
    /**
     * Devuelve el ícono y colores para el badge de una notificación.
     * Soporta notificaciones de requisición y de órdenes de trabajo.
     */
    function notification_visual(array $data): array
    {
        if (isset($data['work_order_id'])) {
            return match ($data['event'] ?? '') {
                'created' => ['add_circle', 'text-blue-600', 'bg-blue-100'],
                'assigned', 'assigned_to_technician' => ['assignment_ind', 'text-blue-600', 'bg-blue-100'],
                'started', 'resumed' => ['play_arrow', 'text-blue-600', 'bg-blue-100'],
                'paused' => ['pause', 'text-amber-600', 'bg-amber-100'],
                'completed' => ['task_alt', 'text-green-600', 'bg-green-100'],
                'cancelled' => ['cancel', 'text-red-600', 'bg-red-100'],
                default => ['assignment', 'text-gray-600', 'bg-gray-100'],
            };
        }

        return ($data['status'] ?? '') === 'approved'
            ? ['check_circle', 'text-green-600', 'bg-green-100']
            : ['cancel', 'text-red-600', 'bg-red-100'];
    }
}