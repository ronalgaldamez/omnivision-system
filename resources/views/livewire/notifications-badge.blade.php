<div class="relative inline-flex items-center">
    <span class="material-symbols-outlined text-gray-500 text-2xl cursor-pointer">notifications</span>
    @if($pendingNocTickets > 0)
        <span
            class="absolute -top-1 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
            {{ $pendingNocTickets }}
        </span>
    @endif

    {{-- Polling cada 30 segundos para mantener actualizado el contador --}}
    @if(Auth::check() && Auth::user()->can('access noc panel'))
        <div wire:poll.30s="updateCount" class="hidden"></div>
    @endif
</div>