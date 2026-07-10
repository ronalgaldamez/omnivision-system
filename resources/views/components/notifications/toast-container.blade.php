@props([
    'duration' => 5000,
    'position' => 'bottom-5 right-5',
])

<div x-data="{ toasts: [] }"
    x-on:show-toast.window="toasts.push({ id: Date.now() + Math.random(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => toasts.shift(), {{ $duration }})"
    x-on:show-toasts.window="
        $event.detail.errors.forEach(msg => {
            toasts.push({ id: Date.now() + Math.random(), type: 'error', message: msg });
            setTimeout(() => toasts.shift(), {{ $duration }});
        });
    "
    {{ $attributes->merge(['class' => "fixed {$position} z-50 flex flex-col gap-2"]) }}>
    <template x-for="t in toasts" :key="t.id">
        <div :class="{
            'bg-green-600': t.type === 'success',
            'bg-red-600': t.type === 'error',
            'bg-blue-600': t.type === 'info',
            'bg-amber-600': t.type === 'warning'
        }" class="text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 transition-all duration-300"
            x-transition:enter="transform ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0">
            <span x-show="t.type === 'success'" class="material-symbols-outlined">check_circle</span>
            <span x-show="t.type === 'error'" class="material-symbols-outlined">error</span>
            <span x-show="t.type === 'info'" class="material-symbols-outlined">info</span>
            <span x-show="t.type === 'warning'" class="material-symbols-outlined">warning</span>
            <span x-text="t.message" class="text-sm font-medium"></span>
        </div>
    </template>
</div>
