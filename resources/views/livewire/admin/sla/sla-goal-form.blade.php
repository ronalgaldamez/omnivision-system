<div class="max-w-3xl mx-auto">
    <x-ui.card icon="{{ $goalId ? 'edit' : 'add_circle' }}" title="{{ $goalId ? 'Editar' : 'Nueva' }} Meta SLA" subtitle="{{ $goalId ? 'Modificá los parámetros de la meta SLA' : 'Definí un nuevo objetivo de tiempo de respuesta' }}">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-forms.group name="priority" label="Prioridad">
                    <x-ui.select wire:model="priority">
                        <option value="P1">P1 - Crítico</option>
                        <option value="P2">P2 - Alta</option>
                        <option value="P3">P3 - Media</option>
                        <option value="P4">P4 - Baja</option>
                    </x-ui.select>
                </x-forms.group>

                <x-forms.group name="service_type_id" label="Tipo de Servicio">
                    <x-ui.select wire:model="service_type_id">
                        <option value="">Todos los servicios</option>
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-forms.group>

                <x-forms.group name="minutes" label="Tiempo Objetivo (minutos)">
                    <x-ui.input type="number" wire:model="minutes" min="1" max="43200" placeholder="Ej: 240 (4 horas)" />
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
                </x-forms.group>

                <x-forms.group name="is_active" label="Activo">
                    <label class="relative inline-flex h-6 w-11 items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                    </label>
                </x-forms.group>
            </div>

            <x-forms.group name="description" label="Descripción (opcional)">
                <x-ui.textarea wire:model="description" rows="2" placeholder="Ej: Tickets críticos deben resolverse en máximo 4 horas" />
            </x-forms.group>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.sla.goals.index') }}">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="save">{{ $goalId ? 'Actualizar' : 'Guardar' }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>