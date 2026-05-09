@props(['title', 'active' => false])

<div {{ $attributes->merge(['class' => 'admin-sidebar-group mb-5 last:mb-0']) }}>
    <div class="px-3 mb-1">
        <h3 class="admin-sidebar-group-title opacity-40 select-none">{{ $title }}</h3>
    </div>

    <div class="admin-sidebar-group-panel flex flex-col gap-0.5">
        {{ $slot }}
    </div>
</div>
