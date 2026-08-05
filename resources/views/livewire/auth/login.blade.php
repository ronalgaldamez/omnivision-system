{{-- Contenedor con fondo Vanta --}}
<div id="vanta-bg" class="min-h-screen flex items-center justify-center p-4 relative">

    <div class="w-full max-w-sm relative z-10">

        {{-- Tarjeta principal (omnivision-design) --}}
        <x-ui.card overflow="visible">
            <div class="text-center">
                @php($loginLogo = setting('logo_global'))
                @php($loginLogoVersion = setting('logo_global_version'))
                @if ($loginLogo)
                    <img src="{{ asset('storage/' . $loginLogo) }}?v={{ $loginLogoVersion }}" alt="Logo"
                        class="h-20 w-auto max-w-[200px] object-contain mx-auto mb-4">
                @else
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-blue-600 text-3xl">grid_view</span>
                    </div>
                @endif
                <h2 class="text-lg font-semibold text-gray-800">Acceso al sistema</h2>
                <p class="text-sm text-gray-500 mt-0.5">Ingresa tus credenciales para continuar</p>
            </div>

            <form wire:submit="login" class="mt-6 space-y-4">

                {{-- Campo Email --}}
                <x-forms.group name="email" label="Correo electrónico">
                    <x-ui.input type="email" wire:model="email" icon="alternate_email"
                        placeholder="ejemplo@correo.com" required />
                </x-forms.group>

                {{-- Campo Contraseña (toggle Alpine) --}}
                <x-forms.group name="password" label="Contraseña">
                    <div x-data="{ show: false }" class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none">lock</span>
                        <input :type="show ? 'text' : 'password'" wire:model="password" id="password"
                            placeholder="••••••••" required
                            class="w-full rounded-lg border text-sm transition pl-10 pr-10 py-2.5 border-gray-200 bg-gray-50 text-gray-900 focus:border-gray-400 focus:bg-white {{ $errors->has('password') ? 'border-red-300 bg-red-50 text-red-900' : '' }}">
                        <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <span class="material-symbols-outlined text-lg" x-show="!show">visibility</span>
                            <span class="material-symbols-outlined text-lg" x-show="show" x-cloak>visibility_off</span>
                        </button>
                    </div>
                </x-forms.group>

                {{-- Recordarme --}}
                <x-ui.checkbox wire:model="remember" label="Recordarme" />

                {{-- Botón submit --}}
                <x-ui.button type="submit" variant="primary" icon="login" class="w-full justify-center">
                    Iniciar sesión
                </x-ui.button>

            </form>

            {{-- Contacto con soporte --}}
            <div class="mt-4 pt-4 border-t border-gray-100">
                <x-ui.button variant="secondary" icon="headset_mic" href="mailto:omnivision.dev@gmail.com"
                    class="w-full justify-center">
                    Contactar con soporte
                </x-ui.button>
                <p class="text-center text-xs text-gray-400 mt-2">¿Problemas para iniciar sesión? Escríbenos y te
                    ayudamos.</p>
            </div>

            {{-- Error de sesión --}}
            @if (session('error'))
                <x-ui.alert variant="danger" class="mt-4">{{ session('error') }}</x-ui.alert>
            @endif
        </x-ui.card>

        {{-- Footer --}}
        <p class="text-center text-xs text-white/40 mt-4">
            &copy; {{ date('Y') }} OMNIVISIÓN. Todos los derechos reservados.
        </p>

    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
    <script>
        VANTA.GLOBE({
            el: "body",
            mouseControls: true,
            touchControls: true,
            color: 0xebd7da,
            backgroundColor: 0x0f172a,
            points: 12.00,
            maxDistance: 22.00,
            spacing: 18.00
        });
    </script>
@endpush
