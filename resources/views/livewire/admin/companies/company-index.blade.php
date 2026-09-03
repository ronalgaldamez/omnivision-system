<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500 text-xl">apartment</span>
                Empresas
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Entidades legales (razón social) que agrupan sucursales</p>
        </div>
        <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.companies.create') }}">
            Nueva empresa
        </x-ui.button>
    </div>

    <x-ui.input type="text" wire:model.live="search" placeholder="Buscar por razón social, nombre comercial o NIT..." icon="search" class="mb-5" />

    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                            Razón social
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">storefront</span>
                            Nombre comercial
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">category</span>
                            Tipo
                        </div>
                    </th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">pin</span>
                            NIT
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">store</span>
                            Sucursales
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">toggle_on</span>
                            Estado
                        </div>
                    </th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                            Acciones
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                    <tr class="hover:bg-gray-50/80 transition {{ !$company->is_active ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3">
                            <p class="text-gray-800 font-medium">{{ $company->razon_social }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $company->nombre_comercial ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge variant="{{ $company->tipo === 'sociedad' ? 'info' : 'neutral' }}">
                                {{ $company->tipoLabel() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $company->nit ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <x-ui.badge variant="neutral">{{ $company->branches_count }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $company->id }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition {{ $company->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                <span class="material-symbols-outlined text-sm">{{ $company->is_active ? 'check_circle' : 'block' }}</span>
                                {{ $company->is_active ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.companies.edit', $company->id) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <button wire:click="delete({{ $company->id }})"
                                    onclick="confirm('¿Eliminar esta empresa? Las sucursales quedarán sin empresa asociada.') || event.stopImmediatePropagation()"
                                    class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center bg-gray-50/50">
                            <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">apartment</span>
                            <p class="text-gray-500">No hay empresas registradas</p>
                            <p class="text-sm text-gray-400 mt-1">Haz clic en "Nueva empresa" para agregar una</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($companies->hasPages())
        <div class="mt-5">{{ $companies->links() }}</div>
    @endif

    @if(session('message'))
        <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
    @endif
</div>
