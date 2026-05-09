@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="--font-base: {{ $accessibilityFontSize }}px;"
      data-contrast="{{ auth()->check() && $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ auth()->check() && $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') — Alumco</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell font-sans text-Alumco-gray antialiased">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden isolate bg-gradient-to-br from-Alumco-cream via-Alumco-cream to-Alumco-cyan/20 px-6 py-12 lg:px-20">
        
        <!-- Nubes Superiores -->
        <div class="pointer-events-none absolute top-0 -right-10 z-0 select-none">
            <img src="{{ asset('images/undraw/clouds_top.svg') }}" alt="" class="animate-cloud h-auto w-[35vw] opacity-90 lg:w-[45vw]">
        </div>

        <!-- Logo -->
        <div class="relative z-10 flex justify-center mb-10">
            <a href="{{ route('login') }}" class="transition-transform hover:scale-105 active:scale-95">
                <img
                    src="{{ asset('images/logo/alumco-full.svg') }}"
                    alt="Alumco"
                    class="h-auto w-40 sm:w-48 lg:w-56"
                >
            </a>
        </div>

        <!-- Card -->
        <div class="relative z-10 mx-auto w-full max-w-3xl animate-page-entry">
            <div class="overflow-hidden rounded-3xl border border-white/40 bg-white/80 shadow-2xl backdrop-blur-xl">

                <!-- Card header -->
                <div class="bg-Alumco-blue/90 px-8 py-6 lg:px-12">
                    <p class="font-display text-xs font-black uppercase tracking-[0.22em] text-Alumco-cyan">
                        Error @yield('code', '!!!')
                    </p>
                    <h1 class="mt-1 font-display text-2xl font-black text-white sm:text-3xl">
                        @yield('header-title', 'Algo salió mal')
                    </h1>
                </div>

                <!-- Card body: content left, illustration right -->
                <div class="flex flex-col-reverse items-center gap-10 px-8 py-10 lg:flex-row lg:px-12 lg:py-12">

                    <!-- Error content -->
                    <div class="flex-1 space-y-8">
                        @yield('content')
                    </div>

                    <!-- SVG illustration -->
                    <div class="flex w-40 flex-shrink-0 items-center justify-center lg:w-48">
                        <div class="text-Alumco-blue opacity-80">
                            @yield('illustration')
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 mt-auto pt-10 text-center text-xs font-bold uppercase tracking-widest text-Alumco-gray/40">
            &copy; {{ date('Y') }} Alumco &bull; Sistema de Gestión de Capacitación
        </footer>
    </div>
</body>
</html>
