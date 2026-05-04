@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="scrollbar-gutter: stable; --font-base: {{ $accessibilityFontSize }}px;"
      data-font="{{ $accessibilityPreferences['fontLevel'] }}"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alumco')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <script>
        (function() {
            try {
                var prefs = JSON.parse(localStorage.getItem('alumco-accessibility'));
                if (prefs) {
                    var levels = [18, 20, 22];
                    document.documentElement.style.setProperty('--font-base', levels[prefs.fontLevel || 0] + 'px');
                    document.documentElement.dataset.font = String(prefs.fontLevel || 0);
                    document.documentElement.dataset.contrast = prefs.highContrast ? 'high' : 'default';
                    document.documentElement.dataset.motion = prefs.reducedMotion ? 'reduced' : 'default';
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('css')
    @stack('styles')
</head>
<body class="worker-shell font-sans text-Alumco-gray antialiased min-h-screen flex flex-col">
    @persist('worker-nav-progress')
        <div class="nav-progress-bar" data-nav-progress data-active="false" aria-hidden="true"></div>
    @endpersist

    {{-- Popup de Modo Vista Previa para Admins/Capacitadores --}}
    @if(session('preview_mode') && (auth()->user()->hasAdminAccess() || auth()->user()->isCapacitador()))
    <div class="fixed bottom-20 right-4 lg:bottom-8 lg:right-8 z-[60] bg-amber-500 text-white p-3 rounded-2xl shadow-2xl flex items-center gap-3 ring-4 ring-amber-500/20 max-w-sm transition-all duration-300">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-xl">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- HEADER: Logo + nav + identidad del trabajador --}}
    @persist('worker-topbar')
    <header class="worker-topbar sticky top-0 z-40 border-b border-white/70 py-3 shrink-0">
        {{-- Desktop: grid [logo | nav centrado | usuario] — garantiza centrado real del nav --}}
        <div class="max-w-2xl mx-auto px-5 flex items-center gap-4 lg:max-w-[90rem] lg:px-8 lg:grid lg:grid-cols-[1fr_auto_1fr] lg:gap-0">
            <a href="{{ route('cursos.index') }}" wire:navigate.hover class="worker-focus worker-pill inline-flex items-center gap-3 shrink-0">
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

            {{-- Nav col 2 — Alpine gestiona active state porque el header es @persisted --}}
            <nav x-data="{
                     path: window.location.pathname,
                     get isCursos() { return this.path.startsWith('/cursos') || this.path.startsWith('/modulos'); },
                     get isCalendario() { return this.path.startsWith('/calendario-cursos'); },
                     get isCertificados() { return this.path.startsWith('/mis-certificados'); }
                 }"
                 x-on:livewire:navigated.document="path = window.location.pathname"
                 class="hidden lg:flex items-center gap-1"
                 aria-label="Navegación principal">

                <a href="{{ route('cursos.index') }}"
                   wire:navigate.hover
                   :class="isCursos ? 'bg-Alumco-blue/10 text-Alumco-blue' : 'text-Alumco-gray/60 hover:bg-Alumco-blue/5 hover:text-Alumco-gray'"
                   class="worker-focus inline-flex items-center gap-2.5 rounded-2xl px-4 py-2.5 text-sm font-bold transition-all"
                   :aria-current="isCursos ? 'page' : 'false'">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.967 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.967 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    Mis cursos
                </a>

                <a href="{{ route('calendario-cursos.index') }}"
                   wire:navigate.hover
                   :class="isCalendario ? 'bg-Alumco-blue/10 text-Alumco-blue' : 'text-Alumco-gray/60 hover:bg-Alumco-blue/5 hover:text-Alumco-gray'"
                   class="worker-focus inline-flex items-center gap-2.5 rounded-2xl px-4 py-2.5 text-sm font-bold transition-all"
                   :aria-current="isCalendario ? 'page' : 'false'">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008H15v-.008ZM15 15h.008v.008H15V15Zm0 3h.008v.008H15V18Z" />
                    </svg>
                    Calendario
                </a>

                <a href="{{ route('mis-certificados.index') }}"
                   wire:navigate.hover
                   :class="isCertificados ? 'bg-Alumco-blue/10 text-Alumco-blue' : 'text-Alumco-gray/60 hover:bg-Alumco-blue/5 hover:text-Alumco-gray'"
                   class="worker-focus inline-flex items-center gap-2.5 rounded-2xl px-4 py-2.5 text-sm font-bold transition-all"
                   :aria-current="isCertificados ? 'page' : 'false'">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Certificados
                </a>
            </nav>

            {{-- Lado derecho col 3: desktop (perfil+salir) y mobile (avatar) --}}
            <div class="flex items-center gap-2 ml-auto lg:ml-0 lg:justify-end lg:gap-3">
                <a href="{{ route('perfil.index') }}"
                   wire:navigate.hover
                   class="worker-focus worker-pill group hidden lg:flex items-center gap-3 bg-white/90 px-3 py-2 shadow-sm ring-1 ring-Alumco-blue/10 transition-all hover:bg-white hover:ring-Alumco-blue/30 hover:shadow-md"
                   title="Ver mi perfil">
                    <span class="min-w-0 text-right">
                        <span class="block max-w-48 truncate text-sm font-bold leading-tight text-Alumco-gray group-hover:text-Alumco-blue transition-colors">{{ auth()->user()->name }}</span>
                    </span>
                    <span class="avatar-btn flex h-11 w-11 items-center justify-center rounded-full bg-Alumco-blue text-sm font-black text-white shadow-sm select-none transition-transform group-hover:scale-[1.02]">
                        {{ $initials }}
                    </span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="hidden lg:block shrink-0">
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

                <a href="{{ route('perfil.index') }}"
                   wire:navigate.hover
                   class="avatar-btn worker-focus lg:hidden w-10 h-10 rounded-full bg-Alumco-blue text-white font-display font-black text-sm flex items-center justify-center shadow-sm select-none"
                   aria-label="Ir a mi perfil">
                    {{ $initials }}
                </a>
            </div>
            @endauth
        </div>
    </header>
    @endpersist

    <div class="worker-page flex-1">
        {{-- Bottom nav mobile (overridable para páginas como evaluaciones) --}}
        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif

        @sectionMissing('bottom-nav')
            @include('partials.bottom-nav')
        @endif

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="pb-28 w-full max-w-2xl mx-auto lg:max-w-[90rem] lg:pb-12 lg:px-8">
            @php
                $navigationPageKind = trim($__env->yieldContent('page_kind')) ?: 'default';
            @endphp
            {{-- El ID dinámico fuerza a Firefox/Safari a re-ejecutar la animación CSS en cada navegación --}}
            <div id="page-content-{{ md5(request()->fullUrl()) }}"
                 class="animate-page-entry"
                 data-nav-content
                 data-page-kind="{{ $navigationPageKind }}"
                 aria-busy="false">
                <div class="nav-skeleton" data-nav-skeleton aria-hidden="true">
                    <div class="nav-skeleton__row nav-skeleton__hero"></div>
                    <div class="nav-skeleton__grid">
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                    </div>
                </div>
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
