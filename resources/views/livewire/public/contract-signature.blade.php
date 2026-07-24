<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        {{-- Error --}}
        @if($error)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-red-500">error</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Enlace no válido</h2>
                <p class="text-gray-600">{{ $error }}</p>
                <p class="text-sm text-gray-400 mt-4">Si creés que esto es un error, comunicate con el agente de ventas.</p>
            </div>
        @endif

        {{-- Expirado --}}
        @if($expired)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-red-500">timer_off</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Enlace expirado</h2>
                <p class="text-gray-600">El tiempo para firmar ha expirado. Contactá a tu agente de ventas para que te genere un nuevo enlace.</p>
            </div>
        @endif

        {{-- Ya firmado --}}
        @if($alreadySigned)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">¡Firma registrada!</h2>
                <p class="text-gray-600">Tu firma ha sido registrada correctamente.</p>
            </div>
        @endif

        {{-- Firma pendiente --}}
        @if($valid)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-indigo-600 px-6 py-5">
                    <h1 class="text-white text-lg font-bold">Firma Electrónica</h1>
                    <p class="text-indigo-200 text-sm mt-1">Contrato de servicios de telecomunicaciones</p>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Datos del cliente --}}
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

                    {{-- Canvas de firma --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Firmá acá abajo con tu dedo o mouse
                        </label>
                        <div x-data="{ canvas: null, ctx: null, drawing: false }"
                            x-init="canvas = $refs.canvas; ctx = canvas.getContext('2d');
                                canvas.width = canvas.offsetWidth;
                                canvas.height = 150;
                                ctx.strokeStyle = '#1e40af';
                                ctx.lineWidth = 2.5;
                                ctx.lineCap = 'round';

                                const startDraw = (e) => {
                                    drawing = true;
                                    const rect = canvas.getBoundingClientRect();
                                    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                    ctx.beginPath();
                                    ctx.moveTo(x, y);
                                };
                                const draw = (e) => {
                                    if (!drawing) return;
                                    e.preventDefault();
                                    const rect = canvas.getBoundingClientRect();
                                    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                                    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                                    ctx.lineTo(x, y);
                                    ctx.stroke();
                                };
                                const endDraw = () => { drawing = false; };

                                canvas.addEventListener('mousedown', startDraw);
                                canvas.addEventListener('mousemove', draw);
                                canvas.addEventListener('mouseup', endDraw);
                                canvas.addEventListener('mouseleave', endDraw);
                                canvas.addEventListener('touchstart', startDraw, { passive: true });
                                canvas.addEventListener('touchmove', draw, { passive: false });
                                canvas.addEventListener('touchend', endDraw);
                            "
                            class="space-y-3">
                            <canvas x-ref="canvas"
                                class="w-full h-[150px] bg-white border-2 border-dashed border-gray-300 rounded-xl cursor-crosshair"></canvas>
                            <div class="flex gap-2">
                                <button type="button" @click="ctx.clearRect(0, 0, canvas.width, canvas.height)"
                                    class="text-sm px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                    Limpiar
                                </button>
                                <button type="button"
                                    @click="(function(){
                                        const data = canvas.toDataURL('image/png');
                                        $wire.call('saveSignature', data);
                                    })()"
                                    class="text-sm px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition font-medium">
                                    Firmar
                                </button>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
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

                        </div>
                    </div>

                    <p class="text-xs text-gray-400 text-center">
                        Al firmar, aceptás los términos y condiciones del contrato de servicios.
                        Esta firma electrónica tiene validez legal según la legislación de El Salvador.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
