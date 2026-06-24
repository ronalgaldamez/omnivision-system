<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500">timer</span>
                    Metas SLA
                </h1>
                <p class="text-sm text-gray-500 mt-1">Configuración de tiempos objetivo por prioridad y tipo de servicio</p>
            </div>
            <a href="{{ route('admin.sla.goals.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Nueva Meta SLA
            </a>
        </div>

        <div class="p-6 space-y-5">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" wire:model.live="search" placeholder="Buscar meta SLA..."
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-sm">
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Prioridad</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Tipo de Servicio</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Tiempo Objetivo</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Descripción</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Activo</th>
                            <th class="px-4 py-3 text-center text-gray-600 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($goals as $goal)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3">
                                    @php
                                        $colors = ['P1' => 'red', 'P2' => 'orange', 'P3' => 'yellow', 'P4' => 'green'];
                                        $bg = $colors[$goal->priority] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $bg }}-100 text-{{ $bg }}-700">
                                        {{ $goal->priority }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $goal->serviceType?->name ?? 'Todos' }}</td>
                                <td class="px-4 py-3 text-gray-700 font-mono">
                                    @if($goal->minutes >= 1440)
                                        {{ intdiv($goal->minutes, 1440) }}d {{ intdiv($goal->minutes % 1440, 60) }}h
                                    @elseif($goal->minutes >= 60)
                                        {{ intdiv($goal->minutes, 60) }}h {{ $goal->minutes % 60 }}m
                                    @else
                                        {{ $goal->minutes }}m
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $goal->description ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="toggleActive({{ $goal->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition
                                        {{ $goal->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        <span class="material-symbols-outlined text-sm">{{ $goal->is_active ? 'check_circle' : 'cancel' }}</span>
                                        {{ $goal->is_active ? 'Sí' : 'No' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.sla.goals.edit', $goal->id) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <button wire:click="delete({{ $goal->id }})"
                                            onclick="confirm('¿Eliminar esta meta SLA?') || event.stopImmediatePropagation()"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center bg-gray-50/50">
                                    <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">timer_off</span>
                                    <p class="text-gray-500">No hay metas SLA configuradas</p>
                                    <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva Meta SLA" para agregar una</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($goals->hasPages())
                <div class="mt-5">{{ $goals->links() }}</div>
            @endif

            @if(session('message'))
                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 px-4 py-3 rounded-lg border border-green-200">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    {{ session('message') }}
                </div>
            @endif
        </div>
    </div>
</div>
