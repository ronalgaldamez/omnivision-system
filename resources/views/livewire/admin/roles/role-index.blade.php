<div class="max-w-7xl mx-auto">
    <x-ui.card icon="security" title="{{ __('roles.title') }}" subtitle="{{ __('roles.subtitle') }}">
        <x-slot:headerActions>
            <x-ui.button variant="primary" icon="add_circle" href="{{ route('admin.roles.create') }}">
                {{ __('roles.new_role') }}
            </x-ui.button>
        </x-slot:headerActions>

        <x-ui.input type="text" wire:model.live="search" placeholder="{{ __('roles.search') }}" icon="search" class="mb-5" />

        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">badge</span>
                                {{ __('roles.col_name') }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">lock</span>
                                {{ __('roles.col_permissions') }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">people</span>
                                {{ __('roles.col_users') }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center text-gray-600 font-medium">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-gray-400 text-base">settings</span>
                                {{ __('roles.col_actions') }}
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 text-gray-800">{{ $role->label() }}</td>
                            <td class="px-4 py-3">
                                @if($role->permissions->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(3) as $perm)
                                            <x-ui.badge variant="neutral">{{ $perm->name }}</x-ui.badge>
                                        @endforeach
                                        @if($role->permissions->count() > 3)
                                            <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 3 }} {{ __('roles.more') }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">{{ __('roles.no_permissions') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $role->users()->count() }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="{{ __('roles.edit') }}">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $role->id }})"
                                        onclick="confirm('{{ __('roles.delete') }}?') || event.stopImmediatePropagation()"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="{{ __('roles.delete') }}">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center bg-gray-50/50">
                                <span class="material-symbols-outlined text-gray-300 text-4xl mb-2">inbox</span>
                                <p class="text-gray-500">{{ __('roles.empty_title') }}</p>
                                <p class="text-sm text-gray-400 mt-1">{{ __('roles.empty_hint') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="mt-5">{{ $roles->links() }}</div>
        @endif

        @if(session('message'))
            <x-ui.alert variant="success">{{ session('message') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
    </x-ui.card>
</div>
