<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">edit</span>
                Editar Usuario
            </h1>
            <p class="text-sm text-gray-500 mt-1">Modifica los datos del usuario</p>
        </div>

        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre</label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Nombre completo">
                    @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" wire:model="email"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="correo@ejemplo.com">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rol</label>
                    <select wire:model.live="selectedRole"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="">Seleccione</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedRole') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sucursal</label>
                    <select wire:model="branchId"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="">— Global —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branchId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-700">Usuario activo</span>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $isActive ? 'Puede iniciar sesión.' : 'Acceso bloqueado.' }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" wire:model.live="isActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">Cancelar</a>
                <button type="button" wire:click="save" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">save</span>Actualizar
                </button>
            </div>
        </div>

        @if($selectedRole)
        <div class="border-t border-gray-200">
            <div class="p-6">
                <div class="bg-gray-50/80 rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4 mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Personalizar permisos</span>
                        @if($permissionsPersonalized)
                            <p class="text-xs text-orange-600 mt-0.5">Los permisos del rol <strong>{{ $selectedRole }}</strong> se usaron como plantilla.</p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">Hereda los permisos de <strong>{{ $selectedRole }}</strong>. Activa para modificarlos.</p>
                        @endif
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" wire:model.live="permissionsPersonalized" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>

                @php
                    $icons = ['Inventario'=>'inventory','Proveedores / Compras'=>'shopping_cart','Órdenes de Trabajo'=>'engineering','Técnicos'=>'handyman','Reportes'=>'assessment','Soporte / Tickets'=>'support_agent','Panel NOC'=>'settings_overscan','Clientes'=>'people','Administración'=>'admin_panel_settings','SLA'=>'timer','Campo / Móvil'=>'smartphone','Supervisor de Zona'=>'map'];
                    $categoryColors = ['gates' => ['label' => 'Acceso al módulo', 'color' => 'purple', 'bg' => 'purple-50', 'border' => 'purple-200', 'hover' => 'purple-400', 'checkedBg' => 'purple-100', 'checkedBorder' => 'purple-400', 'ring' => 'purple-500', 'text' => 'purple-600'], 'menus' => ['label' => 'Visible en menú', 'color' => 'blue', 'bg' => 'blue-50/50', 'border' => 'blue-100', 'hover' => 'blue-300', 'checkedBg' => 'blue-100', 'checkedBorder' => 'blue-300', 'ring' => 'blue-500', 'text' => 'blue-600'], 'actions' => ['label' => 'Acciones', 'color' => 'amber', 'bg' => 'amber-50/50', 'border' => 'amber-100', 'hover' => 'amber-300', 'checkedBg' => 'amber-100', 'checkedBorder' => 'amber-300', 'ring' => 'amber-500', 'text' => 'amber-600']];
                @endphp

                <div class="space-y-2">
                    @foreach($tabModules as $tab)
                        @php
                            $g = $grouped[$tab] ?? ['gates'=>[],'menus'=>[],'actions'=>[]];
                            if(!$permissionsPersonalized) {
                                $g['gates']   = array_values(array_intersect($g['gates'], $rolePermNames));
                                $g['menus']   = array_values(array_intersect($g['menus'], $rolePermNames));
                                $g['actions'] = array_values(array_intersect($g['actions'], $rolePermNames));
                            }
                            $has = !empty($g['gates']) || !empty($g['menus']) || !empty($g['actions']);
                            if(!$permissionsPersonalized && !$has) continue;
                            $open = $activeTab === $tab;
                        @endphp
                        <div class="border border-gray-200 rounded-xl overflow-hidden {{ $open ? 'ring-1 ring-blue-200' : '' }}"
                            x-data="{ open: {{ $open ? 'true' : 'false' }} }"
                            x-on:close-accordion.window="if($event.detail !== '{{ $tab }}') open = false">
                            <button type="button"
                                @click="if(open) { open = false } else { $dispatch('close-accordion', '{{ $tab }}'); open = true; $wire.setTab('{{ $tab }}') }"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition text-left">
                                <span class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-lg text-gray-500">{{ $icons[$tab] ?? 'folder' }}</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $tab }}</span>
                                    @if($permissionsPersonalized)
                                        @php $count = count(array_intersect(array_merge($g['gates'],$g['menus'],$g['actions']), $selectedPermissions)) @endphp
                                        <span class="text-xs text-gray-400">({{ $count }})</span>
                                    @endif
                                </span>
                                <span class="material-symbols-outlined text-gray-400 transition-transform {{ $open ? 'rotate-180' : '' }}">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="px-4 py-4 space-y-4 bg-white border-t border-gray-100">
                                @foreach(['gates','menus','actions'] as $cat)
                                    @if(!empty($g[$cat]))
                                    @php $c = $categoryColors[$cat] @endphp
                                    <div>
                                        <span class="text-[10px] font-bold text-{{ $c['color'] }}-500 uppercase tracking-wider">{{ $c['label'] }}</span>
                                        <div class="{{ $permissionsPersonalized ? 'grid grid-cols-1 sm:grid-cols-2 gap-2' : 'flex flex-wrap gap-1.5' }} mt-1">
                                            @foreach($g[$cat] as $perm)
                                                @if($permissionsPersonalized)
                                                    <label class="flex items-center gap-2 text-xs text-gray-700 bg-{{ $c['bg'] }} px-3 py-2 rounded-lg border border-{{ $c['border'] }} hover:border-{{ $c['hover'] }} cursor-pointer transition has-[:checked]:bg-{{ $c['checkedBg'] }} has-[:checked]:border-{{ $c['checkedBorder'] }}">
                                                        <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $perm }}" class="rounded border-gray-300 text-{{ $c['text'] }} focus:ring-{{ $c['ring'] }}">{{ $perm }}
                                                    </label>
                                                @else
                                                    <span class="inline-flex text-xs text-{{ $c['color'] }}-700 bg-{{ $c['bg'] }} px-2.5 py-1 rounded-lg">{{ $perm }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if(session('message'))
            <div class="mx-6 mb-6 flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                <span class="material-symbols-outlined text-green-600">check_circle</span>{{ session('message') }}
            </div>
        @endif
    </div>
</div>
