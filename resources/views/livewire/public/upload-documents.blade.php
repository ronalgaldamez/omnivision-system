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
            {{-- Enlace expirado --}}
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
            {{-- Confirmación --}}
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
        @endif

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 mt-6">Omnivisión · Todos los derechos reservados</p>
    </div>
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
