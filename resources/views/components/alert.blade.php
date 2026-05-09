@props([
    'type' => 'success',
    'message' => null,
])

@php
    $typeClasses = [
        'success' => 'bg-green-50/50 border-green-100 text-green-800 ring-1 ring-green-200/50',
        'error' => 'bg-red-50/50 border-red-100 text-red-800 ring-1 ring-red-200/50',
        'info' => 'bg-Alumco-blue/5 border-Alumco-blue/10 text-Alumco-blue ring-1 ring-Alumco-blue/10',
        'warning' => 'bg-amber-50/50 border-amber-100 text-amber-800 ring-1 ring-amber-200/50',
    ];
    $iconColors = [
        'success' => 'text-green-500',
        'error' => 'text-red-500',
        'info' => 'text-Alumco-blue',
        'warning' => 'text-amber-500',
    ];
    $iconPaths = [
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />',
        'error' => '<path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />',
        'info' => '<path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />',
        'warning' => '<path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />',
    ];
    $classes = $typeClasses[$type] ?? $typeClasses['success'];
    $icon = $iconPaths[$type] ?? $iconPaths['success'];
    $iconColor = $iconColors[$type] ?? $iconColors['success'];
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-2xl flex items-start gap-3 backdrop-blur-sm transition-all animate-page-entry $classes"]) }} role="alert">
    <svg class="w-5 h-5 shrink-0 {{ $iconColor }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        {!! $icon !!}
    </svg>
    <div class="text-sm font-bold leading-tight">
        {{ $message ?? $slot }}
    </div>
</div>
