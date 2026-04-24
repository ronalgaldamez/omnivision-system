<div class="max-w-md mx-auto bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h1 class="text-lg font-semibold mb-4">Escanear QR / Ingresar Código</h1>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Código de retiro</label>
        <div class="flex gap-2">
            <input type="text" wire:model="code" wire:keydown.enter="search"
                class="flex-1 rounded-md border-gray-300 text-sm" placeholder="Ej: A1B2C3 o escanea QR">
            <button wire:click="search" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                Buscar
            </button>
        </div>
        @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
    </div>

    <div id="qr-reader" style="width: 100%; display: none; margin-top: 10px;"></div>
    <div id="qr-reader-results" class="text-sm text-gray-500 mt-1"></div>

    <div class="mt-4">
        <button type="button" id="scanQrBtn"
            class="w-full px-3 py-1.5 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
            📷 Escanear QR
        </button>
    </div>

    @if(session('message'))
        <div class="mt-2 text-sm text-green-600">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mt-2 text-sm text-red-600">{{ session('error') }}</div>
    @endif
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
                scanBtn.textContent = 'Cerrar cámara';
                startScanner();
            } else {
                qrReader.style.display = 'none';
                scanBtn.textContent = '📷 Escanear QR';
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
                    scanBtn.textContent = '📷 Escanear QR';
                },
                (errorMessage) => {
                    document.getElementById('qr-reader-results').innerText = 'Leyendo...';
                }
            ).catch(err => console.log(err));
        }
    </script>
@endpush