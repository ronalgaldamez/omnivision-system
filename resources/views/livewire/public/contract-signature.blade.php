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

        {{-- Ya firmado --}}
        @if($alreadySigned && $contract)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">¡Firma registrada!</h2>
                <p class="text-gray-600 mb-4">Tu firma ha sido registrada correctamente para el contrato.</p>
                @if($contract->contract_digital_code)
                    <p class="text-sm font-mono text-indigo-600 bg-indigo-50 rounded-lg px-4 py-2 inline-block">
                        {{ $contract->contract_digital_code }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Firma pendiente --}}
        @if($valid && !$alreadySigned && $contract)
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-indigo-600 px-6 py-5">
                    <h1 class="text-white text-lg font-bold">Firma Electrónica de Contrato</h1>
                    <p class="text-indigo-200 text-sm mt-1">{{ $contract->contract_digital_code }}</p>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Datos del contrato --}}
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-base">person</span>
                            <p class="font-medium text-gray-800">{{ $contract->client?->name }}</p>
                        </div>
                        @if($contract->plan)
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-base">assignment</span>
                            <p class="text-sm text-gray-600">Plan: <strong>{{ $contract->plan->name }}</strong></p>
                        </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-base">attach_money</span>
                            <p class="text-sm text-gray-600">Precio: <strong>${{ number_format($contract->price, 2) }}</strong>/mes</p>
                        </div>
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
                                    Firmar contrato
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Términos --}}
                    <p class="text-xs text-gray-400 text-center">
                        Al firmar, aceptás los términos y condiciones del contrato de servicios.
                        Esta firma electrónica tiene validez legal según la legislación de El Salvador.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
