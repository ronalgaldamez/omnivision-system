<div class="max-w-4xl mx-auto">
    <x-ui.card icon="upload_file" title="Importar Productos" subtitle="Sube un archivo Excel o CSV con los productos a importar">

        {{-- STEP 1: Upload --}}
        @if($step === 'upload')
        <div class="p-6 space-y-4">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition cursor-pointer"
                x-data="{ dragging: false }"
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave.prevent="dragging = false"
                x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))">
                <span class="material-symbols-outlined text-4xl text-gray-300 mb-3">cloud_upload</span>
                <p class="text-sm text-gray-600 mb-1">Arrastra tu archivo aquí o</p>
                <label class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg cursor-pointer hover:bg-blue-700 transition">
                    <span class="material-symbols-outlined text-base">folder_open</span>
                    Seleccionar archivo
                    <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="hidden" x-ref="fileInput">
                </label>
                <p class="text-xs text-gray-400 mt-3">Formatos: XLSX, XLS, CSV — Máx 10MB</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                <p class="font-medium mb-1">Columnas esperadas:</p>
                <ul class="list-disc list-inside text-blue-700 space-y-0.5">
                    <li><strong>name</strong> (obligatorio) — Nombre del producto</li>
                    <li><strong>sku</strong> — Código único (opcional, se genera automáticamente)</li>
                    <li><strong>brand</strong> o <strong>marca</strong> (obligatorio) — Nombre de la marca</li>
                    <li><strong>category</strong> o <strong>categoria</strong> (obligatorio) — Nombre de la categoría</li>
                    <li><strong>cost</strong> o <strong>costo</strong> — Costo promedio</li>
                    <li><strong>stock</strong> — Stock inicial</li>
                    <li><strong>description</strong> o <strong>descripcion</strong> — Descripción</li>
                </ul>
            </div>
            @error('file') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- STEP 2: Preview --}}
        @if($step === 'preview' && !empty($preview))
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">Vista previa de las primeras <strong>{{ count($preview) }}</strong> filas</p>
                <x-ui.button variant="ghost" wire:click="resetUpload" icon="close">Cancelar</x-ui.button>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm max-h-80 overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                        <tr>
                            @foreach($columns as $col)
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($preview as $row)
                        <tr class="hover:bg-gray-50/80">
                            @foreach($columns as $col)
                            <td class="px-3 py-2 text-gray-700">{{ $row[$col] ?? '—' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="secondary" wire:click="resetUpload">Volver a subir</x-ui.button>
                <x-ui.button variant="primary" wire:click="import" icon="upload" :disabled="$importing">
                    {{ $importing ? 'Importando...' : 'Importar productos' }}
                </x-ui.button>
            </div>
        </div>
        @endif

        {{-- STEP 3: Results --}}
        @if($step === 'results' && $stats)
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
                    <span class="material-symbols-outlined text-3xl text-green-500">check_circle</span>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['imported'] }}</p>
                    <p class="text-xs text-green-600">Importados</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
                    <span class="material-symbols-outlined text-3xl text-red-500">cancel</span>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['skipped'] }}</p>
                    <p class="text-xs text-red-600">Omitidos</p>
                </div>
            </div>
            @if(!empty($stats['errors']))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-800 mb-2">Errores:</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5 max-h-40 overflow-y-auto">
                    @foreach($stats['errors'] as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="flex justify-end">
                <x-ui.button variant="primary" wire:click="resetUpload" icon="add">Importar otro archivo</x-ui.button>
            </div>
        </div>
        @endif

    </x-ui.card>
</div>
