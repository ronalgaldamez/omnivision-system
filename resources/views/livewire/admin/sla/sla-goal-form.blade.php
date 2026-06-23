<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500">{{ $goalId ? 'edit' : 'add_circle' }}</span>
                {{ $goalId ? 'Editar' : 'Nueva' }} Meta SLA
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $goalId ? 'Modifica los parámetros de la meta SLA' : 'Define un nuevo objetivo de tiempo de respuesta' }}
            </p>
        </div>

        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Prioridad</label>
                        <select wire:model="priority"
                            class="w-full py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <option value="P1">P1 - Crítico</option>
                            <option value="P2">P2 - Alta</option>
                            <option value="P3">P3 - Media</option>
                            <option value="P4">P4 - Baja</option>
                        </select>
                        @error('priority') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo de Servicio</label>
                        <select wire:model="service_type_id"
                            class="w-full py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
                            <option value="">Todos los servicios</option>
                            @foreach($serviceTypes as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                        @error('service_type_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tiempo Objetivo (minutos)</label>
                        <input type="number" wire:model="minutes" min="1" max="43200"
                            class="w-full py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                            placeholder="Ej: 240 (4 horas)">
                        @error('minutes') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-400 mt-1">
                            Equivale a
                            @if($minutes >= 1440)
                                {{ intdiv($minutes, 1440) }} día(s), {{ intdiv($minutes % 1440, 60) }} hora(s)
                            @elseif($minutes >= 60)
                                {{ intdiv($minutes, 60) }} hora(s), {{ $minutes % 60 }} minuto(s)
                            @else
                                {{ $minutes }} minuto(s)
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Activo</label>
                        <div class="flex items-center gap-3 pt-2">
                            <button type="button" wire:click="$set('is_active', {{ $is_active ? 'false' : 'true' }})"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                {{ $is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition
                                    {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                            <span class="text-sm {{ $is_active ? 'text-green-700' : 'text-gray-500' }}">
                                {{ $is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción (opcional)</label>
                    <textarea wire:model="description" rows="2"
                        class="w-full py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm"
                        placeholder="Ej: Tickets críticos deben resolverse en máximo 4 horas"></textarea>
                    @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.sla.goals.index') }}"
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition shadow-sm">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition inline-flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        {{ $goalId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
