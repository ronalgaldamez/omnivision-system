<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Tarjeta principal con sombra suave y bordes redondeados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <!-- Cabecera con fondo sutil -->
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-white text-2xl">person_add</span>
                    </div>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Crear cuenta</h1>
                <p class="text-sm text-gray-500 mt-1">Regístrate para acceder al sistema</p>
            </div>

            <!-- Contenido del formulario -->
            <div class="p-6">
                <form wire:submit="register" class="space-y-5">
                    <!-- Campo Nombre completo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">person</span>
                            Nombre completo
                        </label>
                        <div class="relative">
                            <input type="text" wire:model="name" required
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="Juan Pérez">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">badge</span>
                        </div>
                        @error('name')
                            <span class="text-xs text-red-500 mt-1 block flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Campo Correo electrónico -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">mail</span>
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <input type="email" wire:model="email" required
                                class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="ejemplo@correo.com">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">alternate_email</span>
                        </div>
                        @error('email')
                            <span class="text-xs text-red-500 mt-1 block flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">lock</span>
                            Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" wire:model="password" required
                                class="w-full pl-9 pr-10 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="••••••••">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">key</span>
                        </div>
                        @error('password')
                            <span class="text-xs text-red-500 mt-1 block flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Campo Confirmar contraseña -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">verified</span>
                            Confirmar contraseña
                        </label>
                        <div class="relative">
                            <input type="password" wire:model="password_confirmation" required
                                class="w-full pl-9 pr-10 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                                placeholder="••••••••">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">key</span>
                        </div>
                        @error('password_confirmation')
                            <span class="text-xs text-red-500 mt-1 block flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Botón de registro -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                        <span class="material-symbols-outlined text-base">app_registration</span>
                        Registrarse
                    </button>
                </form>

                <!-- Enlace a inicio de sesión -->
                <div class="mt-5 text-center">
                    <span class="text-sm text-gray-600">¿Ya tienes cuenta?</span>
                    <a href="{{ route('login') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium ml-1 transition">
                        Inicia sesión
                    </a>
                </div>

                <!-- Mensaje de error de sesión -->
                @if(session('error'))
                    <div
                        class="mt-4 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-600">error</span>
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Pie de página opcional -->
        <p class="text-center text-xs text-gray-400 mt-4">
            &copy; {{ date('Y') }} OMNIVISIÓN. Todos los derechos reservados.
        </p>
    </div>
</div>