<div class="max-w-lg mx-auto">
    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <!-- Encabezado con fondo sutil -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">qr_code_scanner</span>
                Escanear QR / Ingresar Código
            </h1>
            <p class="text-sm text-gray-500 mt-1">Escanea un código QR o ingresa el código de retiro manualmente</p>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-5">
            <!-- Campo de código -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-gray-400 text-base">key</span>
                    Código de retiro
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" wire:model="code" wire:keydown.enter="search"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: A1B2C3 o escanea QR">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">qr_code</span>
                    </div>
                    <button wire:click="search"
                        class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">search</span>
                        Buscar
                    </button>
                </div>
                @error('code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Visor QR -->
            <div id="qr-reader" style="width: 100%; display: none; margin-top: 10px;"></div>
            <div id="qr-reader-results" class="text-sm text-gray-500 mt-1"></div>

            <!-- Botón de escanear -->
            <button type="button" id="scanQrBtn"
                class="w-full px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition inline-flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">photo_camera</span>
                Escanear QR
            </button>

            <!-- Mensajes de sesión -->
            @if(session('message'))
                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border border-red-200">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrCode;
        const qrReader = document.getElementById('qr-reader');
        const scanBtn = document.getElementById('scanQrBtn');

        scanBtn.addEventListener('click', () => {
            if (qrReader.style.display === 'none') {
                qrReader.style.display = 'block';
                scanBtn.innerHTML = '<span class="material-symbols-outlined text-base">close</span> Cerrar cámara';
                startScanner();
            } else {
                qrReader.style.display = 'none';
                scanBtn.innerHTML = '<span class="material-symbols-outlined text-base">photo_camera</span> Escanear QR';
                if (html5QrCode) {
                    html5QrCode.stop().then(() => console.log('Scanner stopped'));
                }
            }
        });

        function startScanner() {
            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    document.querySelector('[wire\\:model="code"]').value = decodedText;
                    @this.set('code', decodedText);
                    @this.call('search');
                    html5QrCode.stop();
                    qrReader.style.display = 'none';
                    scanBtn.innerHTML = '<span class="material-symbols-outlined text-base">photo_camera</span> Escanear QR';
                },
                (errorMessage) => {
                    document.getElementById('qr-reader-results').innerText = 'Leyendo...';
                }
            ).catch(err => console.log(err));
        }
    </script>
@endpush