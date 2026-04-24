<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Tarjeta principal con sombra suave y bordes redondeados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
            <!-- Cabecera con fondo sutil -->
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-white text-2xl">inventory</span>
                    </div>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Acceso al Sistema</h1>
                <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Contenido del formulario -->
            <div class="p-6">
                <form wire:submit="login" class="space-y-5">
                    <!-- Campo Email -->
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
                            <!-- Botón opcional para mostrar/ocultar contraseña (puedes activarlo con Alpine si deseas) -->
                            <!-- <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button> -->
                        </div>
                        @error('password')
                            <span class="text-xs text-red-500 mt-1 block flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Recordarme y Olvidé contraseña -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="remember"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500/20">
                            <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Botón de envío -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition duration-150 flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                        <span class="material-symbols-outlined text-base">login</span>
                        Iniciar sesión
                    </button>
                </form>

                <!-- Enlace a registro -->
                <div class="mt-5 text-center">
                    <span class="text-sm text-gray-600">¿No tienes cuenta?</span>
                    <a href="{{ route('register') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium ml-1 transition">
                        Regístrate
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