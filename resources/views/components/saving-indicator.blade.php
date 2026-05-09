@props([
    'on' => 'saved',
    'loadingText' => 'Sincronizando',
    'savedText' => 'Guardado',
])

<div {{ $attributes->merge(['class' => 'shrink-0 flex items-center gap-2 h-6']) }}>
    <div wire:loading.delay class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-Alumco-blue/60">
        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>{{ $loadingText }}</span>
    </div>
    <div x-data="{ saved: false }" 
         x-on:{{ $on }}.window="saved = true; setTimeout(() => saved = false, 2000)" 
         x-show="saved" 
         x-cloak 
         x-transition.opacity 
         class="flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-Alumco-green-accessible">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span>{{ $savedText }}</span>
    </div>
</div>
