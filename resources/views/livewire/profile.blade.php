<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">person</span>
                Mi Perfil
            </h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona tu información personal y credenciales de acceso</p>
        </div>

        <div class="p-6 space-y-8">
            <!-- Avatar y nombre -->
            <div class="flex flex-col items-center gap-4">
                <img src="https://api.dicebear.com/7.x/{{ $avatarStyle }}/svg?seed={{ urlencode(auth()->user()->name) }}&size=96"
                    alt="Avatar" class="w-24 h-24 rounded-full border-4 border-white shadow-md">
                <div class="text-center">
                    <h2 class="text-lg font-semibold text-gray-800">{{ auth()->user()->name }}</h2>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ auth()->user()->roles->first()->name ?? 'Sin rol' }}
                        @if(auth()->user()->roles->first()?->prefix)
                            - {{ auth()->user()->roles->first()->prefix }}
                        @endif
                    </p>
                    <button type="button" wire:click="openAvatarModal"
                        class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition">
                        <span class="material-symbols-outlined text-base">swap_horiz</span>
                        Cambiar Avatar
                    </button>
                </div>
            </div>

            <!-- Formulario de información personal -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Información Personal</h3>
                <form wire:submit.prevent="updateProfile" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre</label>
                        <input type="text" wire:model="name"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                        <input type="email" wire:model="email"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition">
                            Actualizar información
                        </button>
                    </div>
                </form>
            </div>

            <!-- Idioma -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-semibold text-gray-800 mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">language</span>
                    Idioma
                </h3>
                <p class="text-sm text-gray-500 mb-4">Seleccioná el idioma del sistema para tu cuenta.</p>
                <div class="max-w-xs">
                    <select wire:model.live="locale"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>

            <!-- Notificaciones Push -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-semibold text-gray-800 mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">notifications_active</span>
                    Notificaciones Push
                </h3>
                <p class="text-sm text-gray-500 mb-4">Recibí notificaciones del sistema operativo aunque no tengas la pestaña del sistema abierta.</p>

                <div x-data="{ subscribed: {{ auth()->user()->pushSubscriptions()->exists() ? 'true' : 'false' }} }">
                    <button type="button"
                        @click="pushToggle().then(ok => { if (ok) subscribed = !subscribed; })"
                        :class="subscribed ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium border transition">
                        <span class="material-symbols-outlined text-base"
                            x-text="subscribed ? 'notifications_off' : 'notifications_active'"></span>
                        <span x-text="subscribed ? 'Desactivar notificaciones push' : 'Activar notificaciones push'"></span>
                    </button>
                    <p class="text-xs text-gray-400 mt-2" x-show="!subscribed">
                        Al activar, el navegador te pedirá permiso para enviar notificaciones.
                    </p>
                </div>
            </div>

            <!-- Formulario de cambio de contraseña -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Cambiar Contraseña</h3>
                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña actual</label>
                        <input type="password" wire:model="current_password"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
                        <input type="password" wire:model="new_password"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                        @error('new_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar nueva contraseña</label>
                        <input type="password" wire:model="new_password_confirmation"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition">
                            Cambiar contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de selección de avatar -->
    @if($showAvatarModal)
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center"
            style="display: none;">
            <div class="relative mx-auto p-5 w-full max-w-lg">
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">face</span>
                            Selecciona tu Avatar
                        </h3>
                        <button wire:click="closeAvatarModal" class="text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            @foreach($availableStyles as $style => $label)
                                <label class="flex flex-col items-center gap-1 cursor-pointer">
                                    <input type="radio" wire:model.live="avatarStyle" value="{{ $style }}" class="sr-only peer">
                                    <img src="https://api.dicebear.com/7.x/{{ $style }}/svg?seed={{ urlencode(auth()->user()->name) }}&size=64"
                                        alt="{{ $label }}"
                                        class="w-16 h-16 rounded-full border-2 peer-checked:border-blue-500 peer-checked:shadow-md transition">
                                    <span class="text-xs text-gray-600 text-center">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end">
                        <button wire:click="closeAvatarModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>