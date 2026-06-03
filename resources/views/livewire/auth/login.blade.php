{{-- Scripts de Vanta NET (Three.js requerido) --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>--}}

{{-- Contenedor con fondo Vanta --}}
<div id="vanta-bg" class="min-h-screen flex items-center justify-center p-4 relative">

    <div class="w-full max-w-sm relative z-10">

        {{-- Tarjeta principal --}}
        <div class="bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 overflow-hidden shadow-2xl">

            {{-- Cabecera --}}
            <div class="px-8 pt-8 pb-6 text-center border-b border-white/10">
                <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-white">Acceso al sistema</h1>
                <p class="text-sm text-white/50 mt-1">Ingresa tus credenciales para continuar</p>
            </div>

            {{-- Formulario --}}
            <div class="px-8 py-7">
                <form wire:submit="login" class="space-y-5">

                    {{-- Campo Email --}}
                    <div>
                        <label class="flex items-center gap-1.5 text-xs font-medium text-white/60 mb-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25" />
                                </svg>
                            </span>
                            <input
                                type="email"
                                wire:model="email"
                                required
                                placeholder="ejemplo@correo.com"
                                class="w-full pl-9 pr-3 py-2.5 text-sm rounded-lg border border-white/20 bg-white/10 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400/60 focus:bg-white/15 transition"
                            >
                        </div>
                        @error('email')
                            <p class="flex items-center gap-1 text-xs text-red-300 mt-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Campo Contraseña --}}
                    <div>
                        <label class="flex items-center gap-1.5 text-xs font-medium text-white/60 mb-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Contraseña
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                                </svg>
                            </span>
                            <input
                                :type="show ? 'text' : 'password'"
                                wire:model="password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-9 pr-10 py-2.5 text-sm rounded-lg border border-white/20 bg-white/10 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400/60 focus:bg-white/15 transition"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition"
                            >
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="flex items-center gap-1 text-xs text-red-300 mt-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Recordarme y Olvidé contraseña --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="w-3.5 h-3.5 rounded border-white/30 bg-white/10 text-blue-500 focus:ring-blue-400/30 focus:ring-2"
                            >
                            <span class="text-xs text-white/50">Recordarme</span>
                        </label>
                        <a href="#" class="text-xs text-blue-300 hover:text-blue-200 font-medium transition">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    {{-- Botón submit --}}
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white text-sm font-medium py-2.5 px-4 rounded-lg transition duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 focus:ring-offset-transparent shadow-lg shadow-blue-900/30"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Iniciar sesión
                    </button>

                </form>

                {{-- Enlace a registro --}}
                <div class="mt-5 pt-5 border-t border-white/10 text-center">
                    <span class="text-xs text-white/40">¿No tienes cuenta?</span>
                    <a href="{{ route('register') }}" class="text-xs text-blue-300 hover:text-blue-200 font-medium ml-1 transition">
                        Regístrate
                    </a>
                </div>

                {{-- Error de sesión --}}
                @if(session('error'))
                    <div class="mt-4 flex items-center gap-2 text-sm text-red-300 bg-red-500/10 border border-red-400/20 px-4 py-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-white/20 mt-4">
            &copy; {{ date('Y') }} OMNIVISIÓN. Todos los derechos reservados.
        </p>

    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>
    <script>
        VANTA.NET({
            el: "body",
            mouseControls: true,
            touchControls: true,
            color: 0x2563eb,
            backgroundColor: 0x0f172a,
            points: 12.00,
            maxDistance: 22.00,
            spacing: 18.00
        });
    </script>
@endpush
</script>