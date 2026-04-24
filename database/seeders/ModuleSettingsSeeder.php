<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class ModuleSettingsSeeder extends Seeder
{
    public function run()
    {
        $modules = config('modules.modules', []);
        foreach ($modules as $key => $value) {
            Setting::updateOrCreate(
                ['key' => 'module_' . $key],
                ['value' => $value ? 'true' : 'false']
            );
        }
        // Opcional: OT obligatoria por defecto
        Setting::updateOrCreate(['key' => 'ot_required'], ['value' => 'false']);
    }
}
