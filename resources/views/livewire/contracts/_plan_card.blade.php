<label class="relative text-left p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer
    {{ $isSelected ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-200 hover:border-gray-300 bg-white hover:shadow-sm' }}">
    <input type="radio" name="plan_id" value="{{ $plan['id'] }}"
        wire:model.live="plan_id"
        class="absolute opacity-0 w-0 h-0" />
    <div class="absolute top-2 right-2 w-6 h-6 rounded-full flex items-center justify-center
        {{ $isSelected ? 'bg-indigo-600' : 'border-2 border-gray-300 bg-white' }}">
        @if($isSelected)
            <span class="material-symbols-outlined text-white text-sm">check</span>
        @endif
    </div>
    <p class="font-bold text-gray-900">{{ $plan['name'] }}</p>
    <div class="mt-1 space-y-0.5">
        @if($plan['speed'])
            <p class="text-xs text-gray-500">Velocidad: {{ $plan['speed'] }}</p>
        @endif
        @if($plan['channels'])
            <p class="text-xs text-gray-500">Canales: {{ $plan['channels'] }}</p>
        @endif
    </div>
    <div class="mt-3 flex items-baseline gap-1">
        <span class="text-lg font-bold text-indigo-700">${{ number_format($effPrice, 2) }}</span>
        <span class="text-xs text-gray-400">/mes</span>
    </div>
    @if($effPrice != $plan['base_price'])
        <p class="text-[10px] text-amber-600 mt-1">Precio especial para esta zona</p>
    @endif
</label>
