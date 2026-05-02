@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="scrollbar-gutter: stable; --font-base: {{ $accessibilityFontSize }}px;"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alumco')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
    @stack('styles')
</head>
<body class="worker-shell font-sans text-Alumco-gray antialiased min-h-screen flex flex-col">

    {{-- Popup de Modo Vista Previa para Admins/Capacitadores --}}
    @if(session('preview_mode') && (auth()->user()->hasAdminAccess() || auth()->user()->isCapacitador()))
    <div class="fixed bottom-20 right-4 lg:bottom-8 lg:right-8 z-[60] bg-amber-500 text-white p-3 rounded-2xl shadow-2xl flex items-center gap-3 ring-4 ring-amber-500/20 max-w-sm transition-all duration-300">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-xl">
                <svg class="w-5 h-5 animate-pulse shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest leading-none mb-0.5">Vista Previa</span>
                <span class="text-[10px] opacity-90 font-medium leading-none">Como Trabajador</span>
            </div>
        </div>
        <div class="w-px h-8 bg-white/30 mx-1"></div>
        <form action="{{ route('admin.preview.toggle') }}" method="POST" class="m-0 flex">
            @csrf
            <button type="submit" class="bg-white text-amber-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider hover:bg-amber-50 transition-colors shadow-sm whitespace-nowrap">
                Salir
            </button>
        </form>
    </div>
    @endif

    {{-- HEADER: Logo + identidad del trabajador --}}
    <header class="worker-topbar sticky top-0 z-40 border-b border-white/70 py-3 shrink-0">
        <div class="max-w-2xl mx-auto px-5 flex items-center justify-between gap-4 lg:max-w-[90rem] lg:px-8">
            <a href="{{ route('cursos.index') }}" wire:navigate class="worker-focus worker-pill inline-flex items-center gap-3">
                <img src="{{ asset('images/logo/alumco-full.svg') }}"
                     alt="Logo Alumco"
                     width="120"
                     height="32"
                     class="h-8 w-auto">
            </a>

            @auth
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <div class="hidden lg:flex lg:items-center lg:gap-3">
                {{-- Link Directo al Perfil (PC) --}}
                <a href="{{ route('perfil.index') }}"
                   wire:navigate
                   class="worker-focus worker-pill group flex items-center gap-3 bg-white/90 px-3 py-2 shadow-sm ring-1 ring-Alumco-blue/10 transition-all hover:bg-white hover:ring-Alumco-blue/30 hover:shadow-md"
                   title="Ver mi perfil">
                    <span class="min-w-0 text-right">
                        <span class="block max-w-64 truncate text-sm font-bold leading-tight text-Alumco-gray group-hover:text-Alumco-blue transition-colors">{{ auth()->user()->name }}</span>
                    </span>
                    <span class="avatar-btn flex h-11 w-11 items-center justify-center rounded-full bg-Alumco-blue text-sm font-black text-white shadow-sm select-none transition-transform group-hover:scale-105">
                        {{ $initials }}
                    </span>
                </a>

                {{-- Botón Salir Directo (PC) --}}
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit"
                            title="Cerrar sesión"
                            class="worker-focus flex h-11 items-center gap-2 rounded-2xl border border-Alumco-coral-accessible/20 bg-white px-5 text-sm font-black text-Alumco-coral-accessible shadow-sm transition-all hover:bg-Alumco-coral-accessible hover:text-white hover:shadow-lg hover:shadow-Alumco-coral/20 active:scale-95">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3h-9m9 0-3-3m3 3-3 3"/>
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-2 lg:hidden">
                <a href="{{ route('perfil.index') }}"
                   class="avatar-btn worker-focus w-10 h-10 rounded-full bg-Alumco-blue text-white font-display font-black text-sm
                          flex items-center justify-center shadow-sm select-none"
                   aria-label="Ir a mi perfil">
                    {{ $initials }}
                </a>
            </div>
            @endauth
        </div>
    </header>

    <div class="worker-page flex-1 lg:flex lg:flex-row lg:gap-8 lg:max-w-[90rem] lg:mx-auto lg:px-8 lg:items-start">
        {{-- SIDEBAR EN PC / BOTTOM EN MOVIL --}}
        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif

        @sectionMissing('bottom-nav')
            @include('partials.bottom-nav')
        @endif

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 pb-28 w-full max-w-2xl mx-auto lg:max-w-none lg:pb-12 lg:px-0">
            {{-- El ID dinámico fuerza a Firefox/Safari a re-ejecutar la animación CSS en cada navegación --}}
            <div id="page-content-{{ md5(request()->fullUrl()) }}" class="animate-page-entry">
                {{-- BANNER CONTEXTUAL (cada vista inyecta el suyo) --}}
                @yield('course-banner')

                <div class="px-4 py-6 lg:pt-8 lg:px-0">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    {{-- Modal Global de Alertas/Avisos --}}
    <div x-data="{ 
            open: false, 
            title: '', 
            message: '',
            type: 'info',
            showAlert(data) {
                this.title = data.title || 'Aviso';
                this.message = data.message || '';
                this.type = data.type || 'info';
                this.open = true;
            }
         }"
         x-on:show-alert.window="showAlert($event.detail)"
         x-cloak
         x-show="open"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-Alumco-gray/60 backdrop-blur-sm"
             @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            
            <div class="p-8 text-center">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-Alumco-blue/10 text-Alumco-blue">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                
                <h3 class="font-display text-2xl font-black text-Alumco-gray" x-text="title"></h3>
                <p class="mt-3 text-lg font-bold text-Alumco-gray/60 leading-relaxed" x-text="message"></p>
                
                <button @click="open = false" 
                        class="mt-8 w-full rounded-2xl bg-Alumco-blue py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:brightness-110 active:scale-[0.98]">
                    Entendido
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
    @include('partials.accessibility-scripts')
</body>
</html>
