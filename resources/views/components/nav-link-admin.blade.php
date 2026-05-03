@props(['active', 'href', 'title'])

@php
$classes = ($active ?? false)
            ? 'admin-sidebar-link admin-sidebar-link--active group'
            : 'admin-sidebar-link group';
@endphp

<a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }} title="{{ $title }}">
    <div class="admin-sidebar-link-icon {{ ($active ?? false) ? 'text-Alumco-cyan' : 'group-hover:text-Alumco-cyan' }} transition-colors duration-200">
        {{ $icon }}
    </div>
    <span class="min-w-0 whitespace-nowrap overflow-hidden text-ellipsis">
        {{ $slot }}
    </span>
</a>
