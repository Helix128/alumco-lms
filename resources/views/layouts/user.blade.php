<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alumco')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-Alumco-cream font-sans text-Alumco-gray antialiased min-h-screen flex flex-col">

    {{-- Banner de Modo Vista Previa para Admins/Capacitadores --}}
    @if(session('preview_mode') && (auth()->user()->hasAdminAccess() || auth()->user()->isCapacitador()))
    <div class="sticky top-0 z-[60] bg-amber-500 text-white px-4 py-2 shadow-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span class="text-xs font-black uppercase tracking-widest">Modo Vista Previa Activo</span>
                <span class="hidden sm:inline-block text-[10px] opacity-80 font-medium">Estás visualizando la plataforma como un Trabajador estándar.</span>
            </div>
            <form action="{{ route('admin.preview.toggle') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white text-amber-600 px-4 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-amber-50 transition-colors shadow-sm">
                    Salir y volver al panel
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- HEADER: Logo + avatar de iniciales --}}
    <header class="bg-white border-b border-gray-100 shadow-sm px-5 py-3 shrink-0">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <img src="{{ asset('images/logo/alumco-full.svg') }}"
                 alt="Logo Alumco"
                 class="h-8 w-auto">
            @auth
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <a href="{{ route('perfil.index') }}"
               class="avatar-btn w-10 h-10 rounded-full bg-Alumco-blue text-white font-display font-black text-sm
                      flex items-center justify-center shadow-sm select-none">
                {{ $initials }}
            </a>
            @endauth
        </div>
    </header>

    {{-- BANNER CONTEXTUAL (cada vista inyecta el suyo) --}}
    @yield('course-banner')

    <div class="flex-1 lg:flex lg:flex-row lg:max-w-[90rem] lg:mx-auto lg:w-full lg:items-start lg:gap-8 lg:px-8">
        {{-- SIDEBAR EN PC / BOTTOM EN MOVIL --}}
        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif

        @sectionMissing('bottom-nav')
            @include('partials.bottom-nav')
        @endif

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 px-4 py-6 pb-28 w-full max-w-2xl mx-auto lg:max-w-5xl lg:pb-10 lg:px-0 lg:pt-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
