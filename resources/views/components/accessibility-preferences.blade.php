@props([
    'title' => 'Preferencias de accesibilidad',
    'description' => null,
])

<div {{ $attributes->merge(['class' => '']) }}>
    <livewire:accessibility-preferences :title="$title" :description="$description" />
</div>
