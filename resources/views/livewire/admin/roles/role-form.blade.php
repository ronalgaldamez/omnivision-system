<div class="max-w-3xl mx-auto">
    <x-ui.card icon="{{ $roleId ? 'edit' : 'add_circle' }}" title="{{ $roleId ? 'Editar' : 'Nuevo' }} Rol" subtitle="{{ $roleId ? 'Modificá los datos y permisos del rol' : 'Registrá un nuevo rol y asignale sus permisos' }}">
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-forms.group name="name" label="Nombre del Rol">
                    <x-ui.input type="text" wire:model="name" placeholder="Ej: administrador" />
                </x-forms.group>

                <x-forms.group name="prefix" label="Prefijo">
                    <x-ui.input type="text" wire:model="prefix" placeholder="Ej: SAC" class="uppercase" />
                </x-forms.group>
            </div>

            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.roles.index') }}">Cancelar</x-ui.button>
                <x-ui.button variant="primary" icon="save" wire:click="save">{{ $roleId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-lg">lock</span>Permisos
            </h2>
            <span class="text-xs text-gray-400">{{ count($selectedPermissions) }} asignados</span>
        </div>

        <div class="p-6">
            @php
                $icons = ['Inventario'=>'inventory','Proveedores'=>'shopping_cart','Órdenes de Trabajo'=>'engineering','Técnicos'=>'handyman','Reportes'=>'assessment','Soporte / Tickets'=>'support_agent','Panel NOC'=>'settings_overscan','Clientes'=>'people','Administración'=>'admin_panel_settings','SLA'=>'timer','Campo / Móvil'=>'smartphone','Supervisor de Zona'=>'map'];
            @endphp

            <div class="space-y-2">
                @foreach($tabModules as $tab)
                    @php
                        $groups = $grouped[$tab] ?? ['gates'=>[],'menus'=>[],'actions'=>[]];
                        $open = $activeTab === $tab;
                    @endphp
                    <div class="border border-gray-200 rounded-xl overflow-hidden {{ $open ? 'ring-1 ring-blue-200' : '' }}"
                        x-data="{ open: {{ $open ? 'true' : 'false' }} }"
                        x-on:close-accordion.window="if($event.detail !== '{{ $tab }}') open = false">
                        <button type="button"
                            @click="
                                if(open) { open = false }
                                else { $dispatch('close-accordion', '{{ $tab }}'); open = true; $wire.setTab('{{ $tab }}') }
                            "
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100 transition text-left">
                            <span class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-lg text-gray-500">{{ $icons[$tab] ?? 'folder' }}</span>
                                <span class="text-sm font-medium text-gray-700">{{ $tab }}</span>
                                @php
                                    $assigned = count(array_intersect(array_merge($groups['gates'],$groups['menus'],$groups['actions']), $selectedPermissions));
                                @endphp
                                <span class="text-xs text-gray-400">({{ $assigned }}/{{ count($groups['gates'])+count($groups['menus'])+count($groups['actions']) }})</span>
                            </span>
                            <span class="material-symbols-outlined text-gray-400 transition-transform {{ $open ? 'rotate-180' : '' }}">expand_more</span>
                        </button>
                        <div x-show="open" x-collapse class="px-4 py-4 space-y-4 bg-white border-t border-gray-100">
                            @if(!empty($groups['gates']))
                            <div>
                                <span class="text-[10px] font-bold text-purple-500 uppercase tracking-wider">Acceso al módulo</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                    @foreach($groups['gates'] as $perm)
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 bg-purple-50 px-3 py-2 rounded-lg border border-purple-200 hover:border-purple-400 cursor-pointer transition has-[:checked]:bg-purple-100 has-[:checked]:border-purple-400">
                                            <input type="checkbox" value="{{ $perm }}" wire:model="selectedPermissions" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">{{ $perm }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if(!empty($groups['menus']))
                            <div>
                                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Visible en menú</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                    @foreach($groups['menus'] as $perm)
                                        <label class="flex items-center gap-2 text-xs text-gray-700 bg-blue-50/50 px-3 py-2 rounded-lg border border-blue-100 hover:border-blue-300 cursor-pointer transition has-[:checked]:bg-blue-100 has-[:checked]:border-blue-300">
                                            <input type="checkbox" value="{{ $perm }}" wire:model="selectedPermissions" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">{{ $perm }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @if(!empty($groups['actions']))
                            <div>
                                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Acciones</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                    @foreach($groups['actions'] as $perm)
                                        <label class="flex items-center gap-2 text-xs text-gray-700 bg-amber-50/50 px-3 py-2 rounded-lg border border-amber-100 hover:border-amber-300 cursor-pointer transition has-[:checked]:bg-amber-100 has-[:checked]:border-amber-300">
                                            <input type="checkbox" value="{{ $perm }}" wire:model="selectedPermissions" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">{{ $perm }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(session('message'))
            <div class="mx-6 mb-6">
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            </div>
        @endif
    </div>
</div>