<div class="max-w-2xl mx-auto">
    <x-ui.card icon="person_add" title="Nuevo Usuario" subtitle="Registra un nuevo usuario en el sistema">
        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                    Nombre
                </label>
                <div class="relative">
                    <input type="text" wire:model="name"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Nombre completo">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">edit_note</span>
                </div>
                @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">mail</span>
                    Email
                </label>
                <div class="relative">
                    <input type="email" wire:model="email"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="correo@ejemplo.com">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alternate_email</span>
                </div>
                @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">lock</span>
                    Contraseña
                </label>
                <div class="relative">
                    <input type="password" wire:model="password"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="••••••••">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">key</span>
                </div>
                @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">verified</span>
                    Confirmar contraseña
                </label>
                <div class="relative">
                    <input type="password" wire:model="password_confirmation"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="••••••••">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">key</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">security</span>
                    Rol
                </label>
                <div class="relative">
                    <select wire:model="selectedRole"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">Seleccione</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">admin_panel_settings</span>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                @error('selectedRole') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">store</span>
                    Sucursal
                </label>
                <div class="relative">
                    <select wire:model="branchId"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">— Global (todas las sucursales) —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">store</span>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                @error('branchId') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                    Rol Técnico
                </label>
                <div class="relative">
                    <select wire:model="techRole"
                        class="w-full pl-9 pr-8 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm appearance-none">
                        <option value="">— Sin rol técnico —</option>
                        <option value="encargado">Encargado (Principal)</option>
                        <option value="auxiliar">Auxiliar</option>
                    </select>
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">engineering</span>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                @error('techRole') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.users.index') }}">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="save">Guardar</x-ui.button>
            </div>
        </form>

        @if(session('message'))
            <div class="mt-4">
                <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
            </div>
        @endif
    </x-ui.card>
</div>