@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      style="--font-base: {{ $accessibilityFontSize }}px;"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
        @auth
            @include('partials.accessibility-scripts')
        @endauth
    </body>
</html>
