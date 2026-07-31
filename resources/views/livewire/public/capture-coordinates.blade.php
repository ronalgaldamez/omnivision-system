<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo / Brand --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center mx-auto shadow-lg shadow-indigo-200">
                <span class="material-symbols-outlined text-white text-3xl">near_me</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mt-3">Capturar Ubicación</h1>
            <p class="text-sm text-gray-500 mt-1">Compartí tu ubicación para registrar las coordenadas de instalación</p>
        </div>

        {{-- Card principal --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
            @if($captured)
                {{-- Coordenadas capturadas --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl text-green-600">check_circle</span>
                    </div>
                    <h2 class="text-lg font-bold text-green-700">¡Ubicación capturada!</h2>

                    <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-left">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Latitud</span>
                            <code class="text-sm font-mono font-medium text-gray-800">{{ $latitude }}</code>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Longitud</span>
                            <code class="text-sm font-mono font-medium text-gray-800">{{ $longitude }}</code>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400">Estas coordenadas ya fueron guardadas. Podés cerrar esta página.</p>
                </div>

            @elseif($expired)
                {{-- Enlace expirado --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl text-orange-600">timer_off</span>
                    </div>
                    <h2 class="text-lg font-bold text-orange-700">Enlace expirado</h2>
                    <p class="text-sm text-gray-500">
                        Este enlace ya no es válido porque pasaron más de 24 horas desde que se generó.
                    </p>
                    <p class="text-sm text-gray-500">
                        Solicitá un nuevo enlace a la persona que te está atendiendo para continuar con el proceso.
                    </p>
                </div>

            @elseif($showManualMode)
                {{-- Modo manual: el cliente escribe sus coordenadas --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600">edit</span>
                        <h2 class="text-lg font-bold text-gray-900">Ingresar coordenadas manualmente</h2>
                    </div>
                    <p class="text-sm text-gray-500">
                        Si no podés compartir tu ubicación automáticamente, ingresá las coordenadas manualmente.
                    </p>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 space-y-1">
                        <p class="font-medium">📱 ¿Cómo obtener las coordenadas?</p>
                        <ol class="list-decimal list-inside space-y-0.5">
                            <li>Abrí <strong>Google Maps</strong> en tu celular</li>
                            <li>Busca la dirección de instalación</li>
                            <li>Mantené presionado el punto exacto hasta que salga un marcador</li>
                            <li>Deslizá hacia arriba la tarjeta de información</li>
                            <li>Copiá los números de <strong>latitud</strong> y <strong>longitud</strong> que aparecen</li>
                        </ol>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Latitud</label>
                            <input type="text" wire:model="latitude" placeholder="13.6929"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            @error('latitude') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Longitud</label>
                            <input type="text" wire:model="longitude" placeholder="-89.2182"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            @error('longitude') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" wire:click="saveManual"
                            class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">save</span>
                            Guardar coordenadas
                        </button>
                        <button type="button" wire:click="$set('showManualMode', false)"
                            class="px-4 py-3 bg-gray-100 text-gray-600 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                            Volver
                        </button>
                    </div>
                </div>

            @elseif($error)
                {{-- Error con opción a modo manual --}}
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl text-red-600">error</span>
                    </div>
                    <h2 class="text-lg font-bold text-red-700">Error al capturar</h2>
                    <p class="text-sm text-red-600">{{ $error }}</p>

                    <div class="flex flex-col gap-2">
                        <button type="button" onclick="capturarUbicacion()"
                            class="px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                            Intentar de nuevo
                        </button>
                        <button type="button" wire:click="enableManualMode"
                            class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            Ingresar coordenadas manualmente
                        </button>
                    </div>
                </div>

            @else
                {{-- Aviso de privacidad + Botón de captura --}}
                <div class="space-y-4">
                    {{-- Términos y Condiciones / Aviso de Privacidad --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4" x-data="{ showTerms: false, showPrivacy: false }">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-amber-600 text-xl mt-0.5">privacy_tip</span>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm">Aviso de Privacidad y Términos</h3>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Al compartir tu ubicación, autorizás a <strong>Omnivisión</strong> a capturar tus coordenadas geográficas 
                                <strong>únicamente</strong> con el propósito de agilizar el proceso de instalación del servicio contratado.
                            </p>

                            {{-- Botones para expandir términos / privacidad --}}
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button type="button" @click="showTerms = !showTerms"
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-700 underline underline-offset-2 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm" x-text="showTerms ? 'expand_less' : 'expand_more'">expand_more</span>
                                    <span x-text="showTerms ? 'Ocultar términos' : 'Leer términos y condiciones'"></span>
                                </button>
                                <button type="button" @click="showPrivacy = !showPrivacy"
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-700 underline underline-offset-2 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm" x-text="showPrivacy ? 'expand_less' : 'expand_more'">expand_more</span>
                                    <span x-text="showPrivacy ? 'Ocultar aviso' : 'Ver aviso de privacidad completo'"></span>
                                </button>
                            </div>

                            {{-- Términos y Condiciones expandibles --}}
                            <div x-show="showTerms" x-collapse.duration.300ms
                                class="mt-3 bg-white border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto">
                                <h4 class="text-xs font-bold text-gray-900 mb-2">TÉRMINOS Y CONDICIONES DE CAPTURA DE UBICACIÓN</h4>
                                <div class="text-[11px] text-gray-700 leading-relaxed space-y-2">
                                    <p><strong>1. Finalidad del tratamiento:</strong> La captura de coordenadas geográficas (latitud y longitud) tiene como única finalidad facilitar y agilizar el proceso de instalación de los servicios contratados con Omnivisión, permitiendo a nuestro equipo técnico ubicar con precisión el domicilio o lugar donde se realizará la instalación.</p>
                                    
                                    <p><strong>2. Datos recopilados:</strong> Únicamente se recopilan los siguientes datos: latitud, longitud y la dirección IP desde la cual se realiza la captura. No se almacena historial de ubicaciones, ni se rastrea la ubicación en tiempo real una vez finalizada la captura.</p>
                                    
                                    <p><strong>3. Consentimiento:</strong> Al hacer clic en "Permitir y capturar ubicación", el usuario otorga su consentimiento libre, informado, expreso e inequívoco para el tratamiento de sus datos de geolocalización para los fines aquí descritos.</p>
                                    
                                    <p><strong>4. No divulgación a terceros:</strong> Omnivisión se compromete a no compartir, vender, ceder ni transferir los datos de ubicación a terceros, salvo obligación legal o requerimiento de autoridad competente.</p>
                                    
                                    <p><strong>5. Seguridad de los datos:</strong> Los datos de ubicación se almacenan en bases de datos seguras con acceso restringido únicamente al personal autorizado del área técnica y de instalaciones.</p>
                                    
                                    <p><strong>6. Plazo de conservación:</strong> Las coordenadas geográficas se conservarán mientras el cliente mantenga una relación contractual activa con Omnivisión. Una vez finalizada la relación, podrán ser bloqueadas o eliminadas según lo establecido en la Ley de Protección de Datos Personales.</p>
                                    
                                    <p><strong>7. Derechos del titular:</strong> El usuario tiene derecho a solicitar en cualquier momento el acceso, rectificación, cancelación u oposición al tratamiento de sus datos de ubicación, contactando a nuestro departamento de Protección de Datos a través de los canales oficiales.</p>
                                    
                                    <p><strong>8. Modo manual alternativo:</strong> En caso de que el usuario no desee o no pueda compartir su ubicación automáticamente, Omnivisión ofrece un método alternativo para ingresar las coordenadas manualmente, garantizando la misma protección de datos.</p>
                                    
                                    <p><strong>9. Aceptación:</strong> El marcado de la casilla "He leído y acepto" constituye la aceptación expresa de estos términos y condiciones, así como del aviso de privacidad.</p>
                                </div>
                            </div>

                            {{-- Aviso de Privacidad completo expandible --}}
                            <div x-show="showPrivacy" x-collapse.duration.300ms
                                class="mt-3 bg-white border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto">
                                <h4 class="text-xs font-bold text-gray-900 mb-2">AVISO DE PRIVACIDAD</h4>
                                <div class="text-[11px] text-gray-700 leading-relaxed space-y-2">
                                    <p><strong>Omnivisión</strong>, con domicilio en El Salvador, es el responsable del tratamiento de sus datos personales.</p>
                                    
                                    <p>Sus datos de geolocalización serán tratados con base en lo dispuesto por la <strong>Ley de Protección de Datos Personales</strong> de El Salvador y demás normativa aplicable.</p>
                                    
                                    <p><strong>Datos que recabamos:</strong> Coordenadas geográficas (latitud y longitud), dirección IP, fecha y hora de la captura.</p>
                                    
                                    <p><strong>Finalidad primaria:</strong> Ubicar con precisión el lugar de instalación de los servicios contratados, optimizando las rutas y tiempos del personal técnico.</p>
                                    
                                    <p><strong>Finalidades secundarias:</strong> Mejora continua de nuestros procesos de instalación, análisis estadísticos internos no individualizados y generación de reportes de cobertura geográfica.</p>
                                    
                                    <p><strong>Transferencias de datos:</strong> No se realizarán transferencias nacionales o internacionales de sus datos personales sin su consentimiento, salvo las excepciones previstas en la ley.</p>
                                    
                                    <p><strong>Ejercicio de derechos ARCO:</strong> Usted podrá ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO) enviando su solicitud a nuestro Departamento de Protección de Datos, acompañada de su Documento Único de Identidad (DUI).</p>
                                    
                                    <p><strong>Cambios al aviso de privacidad:</strong> Cualquier modificación a este aviso será notificada a través de nuestros canales oficiales y/o en el sitio web de la empresa.</p>
                                    
                                    <p><strong>Fecha de última actualización:</strong> Julio 2026.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="privacyAccepted"
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-xs text-gray-700">He leído y acepto los términos, condiciones y aviso de privacidad</span>
                    </label>
                </div>

                    {{-- Botón de captura (solo si aceptó privacidad) --}}
                    @if($privacyAccepted)
                        <div class="text-center space-y-3 pt-2">
                            <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center mx-auto animate-pulse">
                                <span class="material-symbols-outlined text-4xl text-indigo-600">my_location</span>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Compartí tu ubicación</h2>
                            <p class="text-sm text-gray-500">
                                Necesitamos tu ubicación para registrar las coordenadas donde se instalará el servicio.
                            </p>

                            <button type="button" onclick="capturarUbicacion()"
                                class="w-full px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">gps_fixed</span>
                                Permitir y capturar ubicación
                            </button>

                            <p class="text-xs text-gray-400 flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-sm">lock</span>
                                Solo se usa para esta instalación · No almacenamos tu historial
                            </p>

                            {{-- Enlace a modo manual --}}
                            <button type="button" wire:click="enableManualMode"
                                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium underline underline-offset-2">
                                No puedo compartir ubicación · Ingresar coordenadas manualmente
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 mt-6">
            Omnivisión · Captura de coordenadas para instalación
        </p>
    </div>

    @push('scripts')
    <script>
        function capturarUbicacion() {
            if (!navigator.geolocation) {
                @this.set('error', 'Tu navegador no soporta geolocalización. Usá un dispositivo móvil o Chrome.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    @this.call('saveCoordinates', position.coords.latitude, position.coords.longitude);
                },
                function(error) {
                    let mensaje = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            mensaje = 'Permiso denegado. Por favor, permití el acceso a la ubicación en la configuración de tu navegador o ingresá las coordenadas manualmente.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mensaje = 'No se pudo obtener la ubicación. Verificá que el GPS esté activado.';
                            break;
                        case error.TIMEOUT:
                            mensaje = 'La solicitud de ubicación tardó demasiado. Intentá de nuevo.';
                            break;
                        default:
                            mensaje = 'Error desconocido al obtener la ubicación.';
                    }
                    @this.set('error', mensaje);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }
    </script>
    @endpush
</div>
