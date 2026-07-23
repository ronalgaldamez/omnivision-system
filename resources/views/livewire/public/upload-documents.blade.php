<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
    <div class="w-full max-w-xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl text-indigo-600">description</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Subir documentos</h1>
            <p class="text-sm text-gray-500 mt-1">Adjuntá los documentos solicitados para tu contrato</p>
        </div>

        @if($expired)
            <x-ui.card>
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-red-500">timer_off</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Enlace expirado</h2>
                    <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                        El tiempo para subir documentos ha expirado. Contactá a tu agente de ventas para que te genere un nuevo enlace.
                    </p>
                </div>
            </x-ui.card>
        @elseif($successMessage)
            <x-ui.card>
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-green-600">check_circle</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">¡Documentos recibidos!</h2>
                    <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                        Gracias, tus documentos han sido subidos correctamente. Tu agente de ventas continuará con el proceso.
                    </p>
                </div>
            </x-ui.card>
        @else
            {{-- Upload form --}}
            <x-ui.card>
                <div class="space-y-5">
                    {{-- DUI Frente --}}
                    <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors
                        {{ $this->isUploaded('dui_front') ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-3xl {{ $this->isUploaded('dui_front') ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Frente) *</p>
                        @if($this->isUploaded('dui_front'))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeUpload('dui_front')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="dui_front" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <button type="button" class="mt-2 w-full px-3 py-2 rounded-lg border border-indigo-200 text-indigo-700 text-xs font-medium hover:bg-indigo-50 transition-colors flex items-center justify-center gap-1.5"
                                x-data
                                @click="openCamera('dui_front', $wire)">
                                <span class="material-symbols-outlined text-sm">photo_camera</span>
                                Capturar con cámara
                            </button>
                            @error('dui_front') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- DUI Reverso --}}
                    <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors
                        {{ $this->isUploaded('dui_back') ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-3xl {{ $this->isUploaded('dui_back') ? 'text-green-500' : 'text-gray-300' }}">badge</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">DUI (Reverso) *</p>
                        @if($this->isUploaded('dui_back'))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeUpload('dui_back')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="dui_back" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <button type="button" class="mt-2 w-full px-3 py-2 rounded-lg border border-indigo-200 text-indigo-700 text-xs font-medium hover:bg-indigo-50 transition-colors flex items-center justify-center gap-1.5"
                                x-data
                                @click="openCamera('dui_back', $wire)">
                                <span class="material-symbols-outlined text-sm">photo_camera</span>
                                Capturar con cámara
                            </button>
                            @error('dui_back') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Recibo de luz --}}
                    <div class="border-2 border-dashed rounded-xl p-5 text-center transition-colors
                        {{ $this->isUploaded('receipt') ? 'border-green-300 bg-green-50' : 'border-gray-300 hover:border-indigo-300' }}">
                        <span class="material-symbols-outlined text-3xl {{ $this->isUploaded('receipt') ? 'text-green-500' : 'text-gray-300' }}">receipt</span>
                        <p class="text-sm font-medium text-gray-700 mt-1">Recibo de luz</p>
                        @if($this->isUploaded('receipt'))
                            <p class="text-xs text-green-600 mt-1">✓ Subido</p>
                            <button wire:click="removeUpload('receipt')"
                                class="text-xs text-red-600 hover:text-red-700 mt-1">Eliminar</button>
                        @else
                            <input type="file" wire:model="receipt" accept="image/*,.pdf"
                                class="mt-2 text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <button type="button" class="mt-2 w-full px-3 py-2 rounded-lg border border-indigo-200 text-indigo-700 text-xs font-medium hover:bg-indigo-50 transition-colors flex items-center justify-center gap-1.5"
                                x-data
                                @click="openCamera('receipt', $wire)">
                                <span class="material-symbols-outlined text-sm">photo_camera</span>
                                Capturar con cámara
                            </button>
                            @error('receipt') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Finalizar --}}
                    <div class="pt-2">
                        <button wire:click="finalize"
                            class="w-full py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            Finalizar
                        </button>
                    </div>
                </div>
            </x-ui.card>

            {{-- Camera Overlay --}}
            <div id="camera-overlay" class="fixed inset-0 z-50 bg-black hidden flex flex-col" style="position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100dvh;">
                {{-- Video container (flex-1 para que ocupe el espacio disponible) --}}
                <div class="relative flex-1 flex items-center justify-center bg-black min-h-0">
                    <video id="camera-video" autoplay playsinline
                        class="w-full h-full object-contain"></video>
                    <canvas id="camera-canvas" class="hidden"></canvas>

                    {{-- Corner guides --}}
                    <div class="absolute inset-4 border-2 border-white/40 rounded-2xl pointer-events-none"></div>

                    {{-- Loading --}}
                    <div id="camera-loading" class="absolute inset-0 bg-black/60 flex items-center justify-center hidden">
                        <div class="text-white text-center">
                            <div class="w-10 h-10 border-4 border-indigo-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                            <p class="text-sm">Procesando documento...</p>
                        </div>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="bg-gray-900 px-6 py-5 flex items-center justify-center gap-6 shrink-0">
                    <button onclick="closeCamera()"
                        class="px-5 py-2.5 rounded-xl bg-gray-700 text-white text-sm font-medium hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
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
            // Asegurar que el loading esté oculto al abrir la cámara
            const loading = document.getElementById('camera-loading');
            if (loading) loading.classList.add('hidden');

            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }
            });
            const video = document.getElementById('camera-video');
            video.srcObject = mediaStream;
            await video.play();

            // Init jscanify
            try {
                scanner = new jscanify();
                scannerReady = true;
            } catch (e) {
                scannerReady = false;
            }

            document.getElementById('camera-overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } catch (e) {
            alert('No se pudo acceder a la cámara. Usá la opción de subir archivo.');
        }
    }


    function closeCamera() {
        // Ocultar loading explícitamente
        const loading = document.getElementById('camera-loading');
        if (loading) loading.classList.add('hidden');

        if (mediaStream) {
            mediaStream.getTracks().forEach(t => t.stop());
            mediaStream = null;
        }
        document.getElementById('camera-overlay').classList.add('hidden');
        document.body.style.overflow = '';
        activeField = null;
        activeWire = null;
    }

    async function captureDocument() {
        if (!activeWire || !activeField) return;

        const video = document.getElementById('camera-video');
        const loading = document.getElementById('camera-loading');
        loading.classList.remove('hidden');

        try {
            const w = video.videoWidth;
            const h = video.videoHeight;
            let canvas;

            // Intentar con jscanify (recorte inteligente)
            if (typeof cv !== 'undefined' && scannerReady) {
                try {
                    const resultCanvas = scanner.extractDocument(video);
                    if (resultCanvas && resultCanvas.width > 0 && resultCanvas.height > 0) {
                        canvas = resultCanvas;
                    }
                } catch (e) {
                    // fallback silencioso
                }
            }

            // Fallback: capturar frame completo del video
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                canvas.getContext('2d').drawImage(video, 0, 0);
            }

            // Convertir a base64 y enviar como método Livewire
            const base64Data = canvas.toDataURL('image/jpeg', 0.7);

            const timeout = setTimeout(() => closeCamera(), 15000);

            activeWire.call('uploadFromCamera', activeField, base64Data)
                .catch(() => { clearTimeout(timeout); closeCamera(); });
        } catch (e) {
            closeCamera();
        }
    }


    document.addEventListener('livewire:init', () => {
        // Escuchar evento de documento capturado exitosamente
        Livewire.on('document-captured', ({ field, label }) => {
            const loading = document.getElementById('camera-loading');
            if (loading) {
                // Mostrar check verde brevemente
                loading.innerHTML = `
                    <div class="text-white text-center">
                        <div class="w-16 h-16 rounded-full bg-green-500 flex items-center justify-center mx-auto mb-3">
                            <span class="material-symbols-outlined text-4xl text-white">check</span>
                        </div>
                        <p class="text-sm font-medium">${label} capturado</p>
                        <p class="text-xs text-gray-300 mt-1">Documento subido correctamente</p>
                    </div>
                `;
                loading.classList.remove('hidden');

                // Cerrar la cámara después de 1.2 segundos
                setTimeout(() => {
                    closeCamera();
                    // Restaurar el contenido original del loading para próxima vez
                    loading.innerHTML = `
                        <div class="text-white text-center">
                            <div class="w-10 h-10 border-4 border-indigo-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                            <p class="text-sm">Procesando documento...</p>
                        </div>
                    `;
                }, 1200);
            } else {
                closeCamera();
            }
        });

        // Escuchar evento de error en captura
        Livewire.on('capture-error', ({ message }) => {
            const loading = document.getElementById('camera-loading');
            if (loading) loading.classList.add('hidden');
            alert('Error: ' + message);
        });

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
