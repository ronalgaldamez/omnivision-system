<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
    <div class="w-full max-w-xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl text-indigo-600">assignment</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Portal del Cliente</h1>
            <p class="text-sm text-gray-500 mt-1">Completá los pasos para finalizar tu contratación</p>
        </div>

        @if($expired)
            <x-ui.card>
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-red-500">timer_off</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Enlace expirado</h2>
                    <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                        El tiempo para completar el proceso ha expirado. Contactá a tu agente de ventas para que te genere un nuevo enlace.
                    </p>
                </div>
            </x-ui.card>
        @elseif($alreadySigned)
            <x-ui.card>
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-green-600">check_circle</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">¡Proceso completado!</h2>
                    <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                        Tus documentos, coordenadas y firma han sido registrados correctamente. Tu agente de ventas continuará con el proceso de activación.
                    </p>
                </div>
            </x-ui.card>
        @else
            {{-- Progress Steps --}}
            <div class="flex items-center justify-between mb-6">
                @php
                    $steps = [
                        1 => ['label' => 'Documentos', 'icon' => 'description', 'done' => $this->allDocumentsUploaded()],
                        2 => ['label' => 'Ubicación', 'icon' => 'near_me', 'done' => $coordinatesCaptured],
                        3 => ['label' => 'Firma', 'icon' => 'edit_note', 'done' => $alreadySigned],
                    ];
                @endphp
                @foreach($steps as $num => $s)
                    <div class="flex flex-col items-center relative flex-1">
                        @if($num > 1)
                            <div class="absolute top-4 right-1/2 w-full h-0.5 -z-10
                                {{ $steps[$num - 1]['done'] ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                        @endif
                        <button wire:click="goToStep({{ $num }})"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200
                            {{ $num === $step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : ($s['done'] ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400') }}
                            {{ $s['done'] || $num < $step ? 'cursor-pointer hover:ring-2 hover:ring-indigo-300' : 'cursor-not-allowed' }}">
                            @if($s['done'])
                                <span class="material-symbols-outlined text-sm">check</span>
                            @else
                                <span class="material-symbols-outlined text-sm">{{ $s['icon'] }}</span>
                            @endif
                        </button>
                        <span class="text-[10px] mt-1 font-medium
                            {{ $num === $step ? 'text-indigo-700' : ($s['done'] ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $s['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Paso 1: Documentos --}}
            @if($step === 1)
            <x-ui.card>
                <div class="space-y-4" x-data="{ showConfirm: false, confirmType: '', confirmLabel: '', uploading: '' }">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                        <span class="material-symbols-outlined text-indigo-600 text-sm">description</span>
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Documentos requeridos</span>
                    </div>

                    @foreach([
                        ['field' => 'dui_front', 'label' => 'DUI (Frente)', 'icon' => 'badge'],
                        ['field' => 'dui_back', 'label' => 'DUI (Reverso)', 'icon' => 'badge'],
                        ['field' => 'receipt', 'label' => 'Recibo de luz', 'icon' => 'receipt'],
                        ['field' => 'fachada', 'label' => 'Foto de Fachada', 'icon' => 'home'],
                    ] as $item)
                    <div class="border-2 border-dashed rounded-xl p-4 text-center transition-colors
                        {{ $this->isUploaded($item['field']) ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-2xl {{ $this->isUploaded($item['field']) ? 'text-green-500' : 'text-gray-300' }}">{{ $item['icon'] }}</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">{{ $item['label'] }} *</p>
                        @if($this->isUploaded($item['field']))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button @click="showConfirm = true; confirmLabel = '¿Eliminar {{ $item['label'] }}?'; confirmType = '{{ $item['field'] }}'"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                @change="if($event.target.files[0]) { uploading='{{ $item['field'] }}'; resizeAndUpload($event.target.files[0], '{{ $item['field'] }}', $wire, function(){ uploading=''; }); $event.target.value=''; }" />
                            <div x-show="uploading==='{{ $item['field'] }}'" class="mt-2 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs text-indigo-600">Subiendo...</span>
                            </div>
                            <button type="button"
                                class="mt-2 w-full px-3 py-2 rounded-lg border border-indigo-200 text-indigo-700 text-xs font-medium hover:bg-indigo-50 transition-colors flex items-center justify-center gap-1.5"
                                x-data @click="openCamera('{{ $item['field'] }}', $wire)">
                                <span class="material-symbols-outlined text-sm">photo_camera</span>
                                Capturar con cámara
                            </button>
                        @endif
                    </div>
                    @endforeach

                    {{-- Modal de confirmación --}}
                    <div x-show="showConfirm" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak>
                        <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-auto shadow-xl" @click.stop>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-red-600">help</span>
                                </div>
                                <p class="text-sm font-medium text-gray-800" x-text="confirmLabel"></p>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button @click="showConfirm = false"
                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">Cancelar</button>
                                <button @click="$wire.rejectUpload(confirmType); showConfirm = false"
                                    class="px-4 py-2 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
            @endif

            {{-- Paso 2: Coordenadas --}}
            @if($step === 2)
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                @if($coordinatesCaptured)
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
                        @if(!$client->portal_docs_approved)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center mt-4">
                                <span class="material-symbols-outlined text-2xl text-amber-600">hourglass_empty</span>
                                <h3 class="text-sm font-bold text-amber-800 mt-2">Pendiente de aprobación</h3>
                                <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                    Tus documentos están siendo revisados por un agente.
                                    <strong>Máximo 10 minutos.</strong>
                                </p>
                                <p class="text-xs text-amber-600 mt-2">
                                    El agente se comunicará con vos para coordinar la firma digital.
                                    <br>No es necesario que estés en esta página.
                                </p>
                            </div>
                        @endif
                    </div>
                @elseif($showManualMode)
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">edit</span>
                            <h2 class="text-lg font-bold text-gray-900">Ingresar coordenadas manualmente</h2>
                        </div>
                        <p class="text-sm text-gray-500">Si no podés compartir tu ubicación automáticamente, ingresá las coordenadas manualmente.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 space-y-1">
                            <p class="font-medium">📱 ¿Cómo obtener las coordenadas?</p>
                            <ol class="list-decimal list-inside space-y-0.5">
                                <li>Abrí <strong>Google Maps</strong> en tu celular</li>
                                <li>Busca la dirección de instalación</li>
                                <li>Mantené presionado el punto exacto hasta que salga un marcador</li>
                                <li>Deslizá hacia arriba la tarjeta de información</li>
                                <li>Copiá los números de <strong>latitud</strong> y <strong>longitud</strong></li>
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
                    <div class="space-y-4">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-amber-600 text-xl mt-0.5">privacy_tip</span>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm">Aviso de Privacidad</h3>
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                        Al compartir tu ubicación, autorizás a <strong>Omnivisión</strong> a capturar tus coordenadas geográficas únicamente con el propósito de agilizar el proceso de instalación.
                                    </p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="privacyAccepted"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="text-xs text-gray-700">He leído y acepto</span>
                            </label>
                        </div>

                        @if($privacyAccepted)
                            <div class="text-center space-y-3 pt-2">
                                <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center mx-auto animate-pulse">
                                    <span class="material-symbols-outlined text-4xl text-indigo-600">my_location</span>
                                </div>
                                <h2 class="text-lg font-bold text-gray-900">Compartí tu ubicación</h2>
                                <button type="button" onclick="capturarUbicacion()"
                                    class="w-full px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">gps_fixed</span>
                                    Permitir y capturar ubicación
                                </button>
                                <button type="button" wire:click="enableManualMode"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium underline underline-offset-2">
                                    No puedo compartir ubicación · Ingresar coordenadas manualmente
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            @endif

            {{-- Paso 3: Firma --}}
            @if($step === 3)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-indigo-600 px-6 py-5">
                    <h1 class="text-white text-lg font-bold">Firma Electrónica</h1>
                    <p class="text-indigo-200 text-sm mt-1">Contrato de servicios de telecomunicaciones</p>
                </div>
                <div class="p-6 space-y-5">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-base">person</span>
                            <p class="font-medium text-gray-800">{{ $client->name }}</p>
                        </div>
                        @if($client->document_number)
                        <div class="flex items-center gap-2 mt-1">
                            <span class="material-symbols-outlined text-indigo-500 text-base">badge</span>
                            <p class="text-sm text-gray-600">DUI: {{ $client->document_number }}</p>
                        </div>
                        @endif
                    </div>

                    @if($alreadySigned)
                        <div class="text-center py-4">
                            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                                <span class="material-symbols-outlined text-3xl text-green-600">check_circle</span>
                            </div>
                            <h2 class="text-lg font-bold text-green-700">¡Firma registrada!</h2>
                            <p class="text-sm text-gray-500 mt-1">Tu firma ha sido registrada correctamente.</p>
                        </div>
                    @else
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Firmá acá abajo con tu dedo o mouse</label>
                        <div x-data="{ canvas: null, ctx: null, drawing: false }"
                            x-init="canvas = $refs.canvas; ctx = canvas.getContext('2d');
                                canvas.width = canvas.offsetWidth; canvas.height = 150;
                                ctx.strokeStyle = '#1e40af'; ctx.lineWidth = 2.5; ctx.lineCap = 'round';
                                const startDraw = (e) => { drawing = true; const r = canvas.getBoundingClientRect(); ctx.beginPath(); ctx.moveTo((e.touches ? e.touches[0].clientX : e.clientX) - r.left, (e.touches ? e.touches[0].clientY : e.clientY) - r.top); };
                                const draw = (e) => { if (!drawing) return; e.preventDefault(); const r = canvas.getBoundingClientRect(); ctx.lineTo((e.touches ? e.touches[0].clientX : e.clientX) - r.left, (e.touches ? e.touches[0].clientY : e.clientY) - r.top); ctx.stroke(); };
                                const endDraw = () => { drawing = false; };
                                canvas.addEventListener('mousedown', startDraw); canvas.addEventListener('mousemove', draw); canvas.addEventListener('mouseup', endDraw); canvas.addEventListener('mouseleave', endDraw);
                                canvas.addEventListener('touchstart', startDraw, { passive: true }); canvas.addEventListener('touchmove', draw, { passive: false }); canvas.addEventListener('touchend', endDraw);
                            " class="space-y-3">
                            <canvas x-ref="canvas" class="w-full h-[150px] bg-white border-2 border-dashed border-gray-300 rounded-xl cursor-crosshair"></canvas>
                            <div class="flex gap-2">
                                <button type="button" @click="ctx.clearRect(0, 0, canvas.width, canvas.height)"
                                    class="text-sm px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Limpiar</button>
                                <button type="button"
                                    @click="(function(){ const data = canvas.toDataURL('image/png'); $wire.call('saveSignature', data); })()"
                                    class="text-sm px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition font-medium">Firmar</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="text-xs text-gray-400 text-center">
                        Al firmar, aceptás los términos y condiciones del contrato de servicios. Esta firma electrónica tiene validez legal según la legislación de El Salvador.
                    </p>
                </div>
            </div>
            @endif

            {{-- Camera Overlay --}}
            <div id="camera-overlay" class="fixed inset-0 z-50 bg-black hidden flex flex-col" style="position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100dvh;">
                <div class="relative flex-1 flex items-center justify-center bg-black min-h-0">
                    <video id="camera-video" autoplay playsinline class="w-full h-full object-contain"></video>
                    <canvas id="camera-canvas" class="hidden"></canvas>
                    <div class="absolute inset-4 border-2 border-white/40 rounded-2xl pointer-events-none"></div>
                    <div id="camera-loading" class="absolute inset-0 bg-black/60 flex items-center justify-center hidden">
                        <div class="text-white text-center">
                            <div class="w-10 h-10 border-4 border-indigo-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                            <p class="text-sm">Procesando documento...</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-900 px-6 py-5 flex items-center justify-center gap-6 shrink-0">
                    <button onclick="closeCamera()"
                        class="px-5 py-2.5 rounded-xl bg-gray-700 text-white text-sm font-medium hover:bg-gray-600 transition-colors">Cancelar</button>
                    <button onclick="captureDocument()"
                        class="w-16 h-16 rounded-full bg-white flex items-center justify-center hover:bg-gray-100 transition-colors shadow-lg active:scale-95 transition-transform">
                        <span class="w-12 h-12 rounded-full bg-red-500"></span>
                    </button>
                </div>
            </div>
        @endif

        <p class="text-center text-xs text-gray-400 mt-6">Omnivisión · Todos los derechos reservados</p>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/jscanify.min.js') }}"></script>
    <script async src="https://docs.opencv.org/4.9.0/opencv.js"></script>
    <script>
        let activeField = null;
        let activeWire = null;
        let mediaStream = null;
        let scanner = null;
        let scannerReady = false;

        async function openCamera(field, wire) {
            activeField = field;
            activeWire = wire;
            try {
                const loading = document.getElementById('camera-loading');
                if (loading) loading.classList.add('hidden');
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }
                });
                const video = document.getElementById('camera-video');
                video.srcObject = mediaStream;
                await video.play();
                try { scanner = new jscanify(); scannerReady = true; } catch (e) { scannerReady = false; }
                document.getElementById('camera-overlay').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } catch (e) {
                alert('No se pudo acceder a la cámara. Usá la opción de subir archivo.');
            }
        }

        function closeCamera() {
            const loading = document.getElementById('camera-loading');
            if (loading) loading.classList.add('hidden');
            if (mediaStream) { mediaStream.getTracks().forEach(t => t.stop()); mediaStream = null; }
            document.getElementById('camera-overlay').classList.add('hidden');
            document.body.style.overflow = '';
            activeField = null;
            activeWire = null;
        }

        async function captureDocument() {
            if (!activeWire || !activeField) return;
            const video = document.getElementById('camera-video');
            const loading = document.getElementById('camera-loading');
            if (loading) loading.classList.remove('hidden');
            try {
                if (video.videoWidth === 0 || video.videoHeight === 0) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;
                let canvas;
                if (typeof cv !== 'undefined' && scannerReady) {
                    try { const resultCanvas = scanner.extractDocument(video); if (resultCanvas && resultCanvas.width > 0 && resultCanvas.height > 0) canvas = resultCanvas; } catch (e) { console.warn('jscanify fallback', e); }
                }
                if (!canvas) { canvas = document.createElement('canvas'); canvas.width = w; canvas.height = h; canvas.getContext('2d').drawImage(video, 0, 0, w, h); }
                const MAX_WIDTH = 1200;
                let finalWidth = canvas.width;
                let finalHeight = canvas.height;
                if (finalWidth > MAX_WIDTH) { finalHeight = Math.round((canvas.height * MAX_WIDTH) / canvas.width); finalWidth = MAX_WIDTH; }
                const finalCanvas = document.createElement('canvas');
                finalCanvas.width = finalWidth;
                finalCanvas.height = finalHeight;
                finalCanvas.getContext('2d').drawImage(canvas, 0, 0, finalWidth, finalHeight);
                const base64Data = finalCanvas.toDataURL('image/jpeg', 0.6);
                const timeout = setTimeout(() => { console.warn('Timeout de subida'); closeCamera(); alert('Conexión lenta. Verificá tu red e intentá de nuevo.'); }, 15000);
                activeWire.call('saveBase64File', activeField, base64Data)
                    .then(() => clearTimeout(timeout))
                    .catch((error) => { clearTimeout(timeout); console.error('Error:', error); closeCamera(); });
            } catch (e) { console.error('Error al capturar:', e); closeCamera(); }
        }

        function resizeAndUpload(file, field, wire, done) {
            if (!file) return;
            var isPdf = file.type === 'application/pdf' || file.name.match(/\.pdf$/i);
            var reader = new FileReader();
            reader.onload = function(e) {
                if (isPdf) {
                    wire.call('saveBase64File', field, e.target.result, file.name).then(function() { if (done) done(); }).catch(function() { if (done) done(); });
                    return;
                }
                var img = new Image();
                img.onload = function() {
                    var MAX_WIDTH = 1200;
                    var w = img.width, h = img.height;
                    if (w > MAX_WIDTH) { h = Math.round((h * MAX_WIDTH) / w); w = MAX_WIDTH; }
                    var canvas = document.createElement('canvas');
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    var resizedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                    wire.call('saveBase64File', field, resizedBase64, file.name).then(function() { if (done) done(); }).catch(function() { if (done) done(); });
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function capturarUbicacion() {
            if (!navigator.geolocation) { @this.set('error', 'Tu navegador no soporta geolocalización.'); return; }
            navigator.geolocation.getCurrentPosition(
                function(position) { @this.call('saveCoordinates', position.coords.latitude, position.coords.longitude); },
                function(error) {
                    let mensaje = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED: mensaje = 'Permiso denegado. Permití el acceso o ingresá las coordenadas manualmente.'; break;
                        case error.POSITION_UNAVAILABLE: mensaje = 'No se pudo obtener la ubicación. Verificá que el GPS esté activado.'; break;
                        case error.TIMEOUT: mensaje = 'La solicitud tardó demasiado. Intentá de nuevo.'; break;
                        default: mensaje = 'Error desconocido.';
                    }
                    @this.set('error', mensaje);
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('document-captured', ({ field, label }) => {
                const loading = document.getElementById('camera-loading');
                if (loading) {
                    loading.innerHTML = `<div class="text-white text-center"><div class="w-16 h-16 rounded-full bg-green-500 flex items-center justify-center mx-auto mb-3"><span class="material-symbols-outlined text-4xl text-white">check</span></div><p class="text-sm font-medium">${label} capturado</p><p class="text-xs text-gray-300 mt-1">Documento subido correctamente</p></div>`;
                    loading.classList.remove('hidden');
                    setTimeout(() => { closeCamera(); loading.innerHTML = `<div class="text-white text-center"><div class="w-10 h-10 border-4 border-indigo-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div><p class="text-sm">Procesando documento...</p></div>`; }, 1200);
                } else { closeCamera(); }
            });
            Livewire.on('capture-error', ({ message }) => { const loading = document.getElementById('camera-loading'); if (loading) loading.classList.add('hidden'); alert('Error: ' + message); });
            Livewire.on('show-toast', ({ type, message }) => {
                const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-blue-600' };
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-2.5 rounded-lg text-white text-sm shadow-lg ${colors[type] || 'bg-gray-700'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        });
    </script>
@endpush
