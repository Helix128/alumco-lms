<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alumco')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    {{-- Anti-flash: aplica preferencias guardadas antes de renderizar --}}
    <script>
        (function () {
            var fl = localStorage.getItem('alumco-font-level');
            if (fl !== null) {
                var px = [14, 16, 18, 20][parseInt(fl, 10)] || 16;
                document.documentElement.style.setProperty('--font-base', px + 'px');
            }

            var prefs = {};
            try {
                prefs = JSON.parse(localStorage.getItem('alumco-accessibility') || '{}');
            } catch (error) {
                prefs = {};
            }
            document.documentElement.dataset.contrast = prefs.highContrast ? 'high' : 'default';
            document.documentElement.dataset.motion = prefs.reducedMotion ? 'reduced' : 'default';
            document.documentElement.dataset.background = prefs.simpleBackground ? 'simple' : 'textured';
            document.documentElement.dataset.cards = prefs.compactCards ? 'compact' : 'comfortable';
        })();
    </script>
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
            <a href="{{ route('cursos.index') }}" class="worker-focus worker-pill inline-flex items-center gap-3">
                <img src="{{ asset('images/logo/alumco-full.svg') }}"
                     alt="Logo Alumco"
                     class="h-8 w-auto">
            </a>

            @auth
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <div class="relative hidden lg:block" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
                <button type="button"
                        x-on:click="open = !open"
                        x-on:click.outside="open = false"
                        class="worker-focus worker-pill flex items-center gap-3 bg-white/90 px-3 py-2 shadow-sm ring-1 ring-Alumco-blue/10 hover:bg-white"
                        :aria-expanded="open.toString()"
                        aria-haspopup="menu">
                    <span class="min-w-0 text-right">
                        <span class="block max-w-64 truncate text-sm font-bold leading-tight text-Alumco-gray">{{ auth()->user()->name }}</span>
                        <span class="block text-xs font-medium leading-tight text-Alumco-gray/60">Mi cuenta</span>
                    </span>
                    <span class="avatar-btn flex h-11 w-11 items-center justify-center rounded-full bg-Alumco-blue text-sm font-black text-white shadow-sm select-none">
                        {{ $initials }}
                    </span>
                    <svg class="h-4 w-4 text-Alumco-blue transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div x-cloak
                     x-show="open"
                     x-transition
                     class="absolute right-0 mt-3 w-64 rounded-3xl bg-white p-3 shadow-2xl ring-1 ring-Alumco-blue/10"
                     role="menu">
                    <a href="{{ route('perfil.index') }}"
                       class="worker-focus flex items-center gap-3 rounded-2xl px-4 py-3 text-base font-bold text-Alumco-gray hover:bg-Alumco-blue/5"
                       role="menuitem">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.42 0-8 2.24-8 5v1.5h16V19c0-2.76-3.58-5-8-5Z"/>
                            </svg>
                        </span>
                        Ver mi perfil
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-1">
                        @csrf
                        <button type="submit"
                                class="worker-focus flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-base font-bold text-Alumco-coral-accessible hover:bg-Alumco-coral/10"
                                role="menuitem">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-Alumco-coral/10 text-Alumco-coral-accessible">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3h-9m9 0-3-3m3 3-3 3"/>
                                </svg>
                            </span>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
            <a href="{{ route('perfil.index') }}"
               class="avatar-btn worker-focus w-10 h-10 rounded-full bg-Alumco-blue text-white font-display font-black text-sm
                      flex items-center justify-center shadow-sm select-none lg:hidden"
               aria-label="Ir a mi perfil">
                {{ $initials }}
            </a>
            @endauth
        </div>
    </header>

    <div class="worker-page flex-1 lg:flex lg:flex-row lg:gap-[36px] lg:max-w-[1440px] lg:mx-auto lg:px-[36px] lg:items-start">
        {{-- SIDEBAR EN PC / BOTTOM EN MOVIL --}}
        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif

        @sectionMissing('bottom-nav')
            @include('partials.bottom-nav')
        @endif

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 pb-28 w-full max-w-2xl mx-auto lg:max-w-none lg:pb-12 lg:px-0">
            {{-- BANNER CONTEXTUAL (cada vista inyecta el suyo) --}}
            @yield('course-banner')

            <div class="px-4 py-6 lg:pt-8 lg:px-0">
                @yield('content')
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
    {{-- Stores Alpine para preferencias accesibles persistentes --}}
    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.store('fontScale', {
                level: (function () {
                    var storedLevel = parseInt(localStorage.getItem('alumco-font-level') ?? '1', 10);
                    return Number.isInteger(storedLevel) && storedLevel >= 0 && storedLevel <= 3 ? storedLevel : 1;
                })(),
                levels: [14, 16, 18, 20],
                apply: function () {
                    document.documentElement.style.setProperty('--font-base', this.levels[this.level] + 'px');
                },
                currentLabel: function () {
                    return this.levels[this.level] + ' px';
                },
                increase: function () {
                    if (this.level < 3) {
                        this.level++;
                        localStorage.setItem('alumco-font-level', this.level);
                        this.apply();
                    }
                },
                decrease: function () {
                    if (this.level > 0) {
                        this.level--;
                        localStorage.setItem('alumco-font-level', this.level);
                        this.apply();
                    }
                },
                init: function () { this.apply(); }
            });

            Alpine.store('accessibility', {
                highContrast: false,
                reducedMotion: false,
                simpleBackground: false,
                compactCards: false,
                load: function () {
                    var prefs = {};
                    try {
                        prefs = JSON.parse(localStorage.getItem('alumco-accessibility') || '{}');
                    } catch (error) {
                        prefs = {};
                    }
                    this.highContrast = Boolean(prefs.highContrast);
                    this.reducedMotion = Boolean(prefs.reducedMotion);
                    this.simpleBackground = Boolean(prefs.simpleBackground);
                    this.compactCards = Boolean(prefs.compactCards);
                    this.apply();
                },
                apply: function () {
                    document.documentElement.dataset.contrast = this.highContrast ? 'high' : 'default';
                    document.documentElement.dataset.motion = this.reducedMotion ? 'reduced' : 'default';
                    document.documentElement.dataset.background = this.simpleBackground ? 'simple' : 'textured';
                    document.documentElement.dataset.cards = this.compactCards ? 'compact' : 'comfortable';
                },
                persist: function () {
                    localStorage.setItem('alumco-accessibility', JSON.stringify({
                        highContrast: this.highContrast,
                        reducedMotion: this.reducedMotion,
                        simpleBackground: this.simpleBackground,
                        compactCards: this.compactCards
                    }));
                    this.apply();
                },
                init: function () { this.load(); }
            });
        });
    </script>
</body>
</html>
