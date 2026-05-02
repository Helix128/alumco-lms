@props(['active', 'href', 'title'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-3 text-white bg-white/10 border-r-4 border-Alumco-cyan rounded-xl transition-all duration-200 active:scale-[0.98] group'
            : 'flex items-center gap-3 px-3 py-3 text-white/70 hover:text-white hover:bg-white/5 border-r-4 border-transparent hover:border-white/10 rounded-xl transition-all duration-200 active:scale-[0.98] group';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }} title="{{ $title }}">
    <div class="shrink-0 {{ ($active ?? false) ? 'text-Alumco-cyan' : 'group-hover:text-Alumco-cyan' }} transition-colors duration-200">
        {{ $icon }}
    </div>
    <span class="font-medium whitespace-nowrap overflow-hidden text-ellipsis">
        {{ $slot }}
    </span>
</a>
