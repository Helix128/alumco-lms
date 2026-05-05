@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="--font-base: {{ $accessibilityFontSize }}px;"
      data-font="{{ $accessibilityPreferences['fontLevel'] }}"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumco - @yield('title', 'Panel')</title>
    
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
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        .sidebar-transition {
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        [data-motion="reduced"] .sidebar-transition {
            transition: none !important;
        }

        .nav-item-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid var(--color-Alumco-cyan);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(94, 94, 94, 0.2);
            border-radius: 9999px;
        }

        .custom-scrollbar-light::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar-light::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        
        /* Context Menu Styles */
        .context-menu {
            position: fixed;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(94, 94, 94, 0.1);
            z-index: 100;
            min-width: 180px;
            padding: 6px;
            display: none;
        }
        .context-menu.active { display: block; }
        .context-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            color: #4A4A4A;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .context-menu-item:hover {
            background-color: #F3F4F6;
            color: #205099;
        }
    </style>
    @stack('css')
    @stack('styles')
</head>

<body class="admin-shell font-sans text-Alumco-gray h-screen flex flex-col overflow-hidden antialiased"
      x-data="{ sidebarOpen: true, toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; } }">
    @persist('admin-nav-progress')
        <div class="nav-progress-bar" data-nav-progress data-active="false" aria-hidden="true"></div>
    @endpersist

    <!-- Topbar -->
    @persist('admin-topbar')
    <header class="admin-topbar admin-topbar-persistent border-b border-white/10 px-6 py-3 flex items-center justify-between z-[80] shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center">
                <a href="{{ auth()->user()->hasAdminAccess() ? route('admin.dashboard.index') : route('capacitador.dashboard') }}" wire:navigate.hover class="flex items-center text-white">
                    <x-logo-alumco class="h-8 w-auto" width="120" height="32" />
                </a>
            </div>
            <div class="admin-topbar-divider h-6 w-px hidden md:block"></div>
            <h1 class="hidden md:block font-display font-black text-lg text-white tracking-tight">
                @yield('header_title', 'Centro de Gestión')
            </h1>
        </div>

        <div class="flex items-center gap-4">
            @auth
            @include('partials.accessibility-modal', [
                'buttonClass' => 'worker-focus admin-topbar-action hidden sm:inline-flex',
            ])

            @if(auth()->user()->hasAdminAccess())
                <form action="{{ route('admin.preview.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            data-active="{{ session('preview_mode') ? 'true' : 'false' }}"
                            class="worker-focus admin-topbar-action admin-topbar-action--preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ session('preview_mode') ? 'Saliendo de Vista Previa' : 'Ver como Usuario' }}
                    </button>
                </form>
            @endif

            <div class="text-right hidden sm:block">
                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest leading-none mb-0.5">Administrador</p>
                <p class="text-sm font-bold text-white leading-none tracking-tight">{{ auth()->user()->name }}</p>
            </div>
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <a href="{{ route('admin.perfil.index') }}"
               wire:navigate.hover
               class="worker-focus admin-avatar-button select-none">
                {{ $initials }}
            </a>
            <div class="sm:hidden">
                @include('partials.accessibility-modal', [
                    'buttonClass' => 'worker-focus admin-icon-button',
                    'showLabel' => false,
                ])
            </div>
            @endauth
        </div>
    </header>
    @endpersist

    <div class="flex-1 flex overflow-hidden">

        <!-- Expandable Sidebar -->
        <aside id="sidebar"
               class="admin-sidebar sidebar-transition bg-Alumco-blue flex flex-col z-[70] shrink-0 overflow-hidden w-72"
               :style="sidebarOpen ? '' : 'transform: translateX(-100%); margin-left: -18rem'">
            
            <div class="flex-1 py-5 px-2 flex flex-col gap-1.5 overflow-y-auto custom-scrollbar border-r border-white/10 min-w-[18rem]">
                
                @if(session('preview_mode'))
                    {{-- Opciones de Trabajador en Vista Previa --}}
                    <h2 class="admin-sidebar-section-label mb-2 select-none">Vista Previa: Trabajador</h2>
                    
                    <x-nav-link-admin href="{{ route('cursos.index') }}" :active="request()->routeIs('cursos.*')" title="Mis Cursos">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </x-slot>
                        Mis Cursos
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('calendario-cursos.index') }}" :active="request()->routeIs('calendario-cursos.*')" title="Calendario">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </x-slot>
                        Calendario
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('mis-certificados.index') }}" :active="request()->routeIs('mis-certificados.*')" title="Mis Certificados">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </x-slot>
                        Mis Certificados
                    </x-nav-link-admin>

                @else
                {{-- Dashboard Admin --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.dashboard.index') }}" :active="request()->routeIs('admin.dashboard.*')" title="Dashboard">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-slot>
                    Dashboard Analítico
                </x-nav-link-admin>
                @endif

                {{-- Dashboard Capacitador --}}
                @if(auth()->user()->isCapacitador())
                <x-nav-link-admin href="{{ route('capacitador.dashboard') }}" :active="request()->routeIs('capacitador.dashboard')" title="Dashboard">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-slot>
                    Dashboard
                </x-nav-link-admin>
                @endif

                {{-- Estadísticas --}}
                @if(auth()->user()->isCapacitador())
                <x-nav-link-admin href="{{ route('capacitador.estadisticas.index') }}" :active="request()->routeIs('capacitador.estadisticas.*')" title="Estadísticas">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </x-slot>
                    Estadísticas
                </x-nav-link-admin>
                @endif

                {{-- Mis Cursos / Gestión Contenido --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.cursos.index') }}" :active="request()->routeIs('capacitador.*cursos*')" title="Contenido">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </x-slot>
                    Cursos y Material
                </x-nav-link-admin>
                @endif

                {{-- Reportes --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.reportes.index') }}" :active="request()->routeIs('admin.reportes.*')" title="Reportes">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </x-slot>
                    Reportes Académicos
                </x-nav-link-admin>
                @endif

                {{-- Usuarios --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.usuarios.index') }}" :active="request()->routeIs('admin.usuarios.*')" title="Usuarios">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </x-slot>
                    Directorio de usuarios
                </x-nav-link-admin>
                @endif

                {{-- Configuración Global (Solo Dev) --}}
                @if(auth()->user()->isDesarrollador())
                <x-nav-link-admin href="{{ route('dev.configuracion') }}" :active="request()->routeIs('dev.configuracion')" title="Variables">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </x-slot>
                    Variables de Sistema
                </x-nav-link-admin>
                @endif

                {{-- Calendario --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.calendario.index') }}" :active="request()->routeIs('capacitador.calendario.*')" title="Calendario">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </x-slot>
                    Calendario Institucional
                </x-nav-link-admin>
                @endif
                @endif
            </div>

            <!-- Footer Sidebar: Cerrar sesión -->
            <div class="p-3 border-t border-white/10 min-w-[18rem]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="admin-sidebar-link worker-focus w-full text-left text-white/70 hover:text-Alumco-coral hover:bg-Alumco-coral/10 group"
                            title="Cerrar sesión">
                        <svg class="w-6 h-6 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium whitespace-nowrap overflow-hidden text-ellipsis">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10">
            @php
                $navigationPageKind = trim($__env->yieldContent('page_kind')) ?: 'dashboard';
            @endphp
            <div id="admin-content-{{ md5(request()->fullUrl()) }}"
                 class="max-w-[1600px] mx-auto animate-page-entry"
                 data-nav-content
                 data-page-kind="{{ $navigationPageKind }}"
                 aria-busy="false">
                <div class="nav-skeleton nav-skeleton--dense" data-nav-skeleton aria-hidden="true">
                    <div class="nav-skeleton__row nav-skeleton__title"></div>
                    <div class="nav-skeleton__grid nav-skeleton__grid--three">
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                    </div>
                    <div class="nav-skeleton__row nav-skeleton__table"></div>
                </div>
                @yield('content')
            </div>
        </main>
    </div>

    @yield('modals')

    @livewireScripts
    @stack('scripts')
    @include('partials.accessibility-scripts')
</body>
</html>
