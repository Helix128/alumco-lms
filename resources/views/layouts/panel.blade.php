<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumco - Panel</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .rounded-Alumco { border-radius: 8px; }

        .focus-ring {
            outline: none;
            box-shadow: 0 0 0 3px rgba(32, 80, 153, 0.25);
        }

        .filter-card {
            border: 1px solid rgba(94, 94, 94, 0.2);
            border-radius: 8px;
            background: #ffffff;
            padding: 14px;
        }

        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(94, 94, 94, 0.35);
            border-radius: 9999px;
        }

        /* Context Menu Styles */
        .context-menu {
            position: fixed;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(94, 94, 94, 0.2);
            z-index: 50;
            min-width: 160px;
            padding: 4px 0;
            display: none;
        }

        .context-menu.active {
            display: block;
        }

        .context-menu-item {
            display: block;
            width: 100%;
            text-align: left;
            padding: 8px 16px;
            font-size: 14px;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.2s;
        }

        .context-menu-item:hover {
            background-color: #f7fafc;
            color: #2b6cb0;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-Alumco-cream font-sans leading-normal tracking-normal text-Alumco-gray h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-20 bg-Alumco-blue flex flex-col items-center py-6 gap-8 z-10 shrink-0">

        {{-- Dashboard — visible para capacitadores y admins --}}
        @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
        <a href="{{ route('capacitador.dashboard') }}"
           class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Dashboard">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </a>
        @endif

        {{-- Mis Cursos — visible para capacitadores y admins --}}
        @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
        <a href="{{ route('capacitador.cursos.index') }}"
           class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Mis cursos">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </a>
        @endif

        {{-- Reportes — solo admins --}}
        @if(auth()->user()->hasAdminAccess())
        <a href="{{ route('admin.reportes.index') }}"
           class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Reportes">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </a>
        @endif

        {{-- Usuarios — solo admins --}}
        @if(auth()->user()->hasAdminAccess())
        <a href="{{ route('admin.usuarios.index') }}"
           class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Usuarios">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </a>
        @endif

        {{-- Calendario -- visible para capacitadores y admins --}}
        @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
        <a href="{{ route('calendario.index') }}" 
            class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Calendario">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </a>
        @endif

        <!-- Cerrar sesión -->
        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
            @csrf
            <button type="submit"
                    class="text-white hover:text-Alumco-cyan cursor-pointer bg-transparent border-none focus-ring p-1 rounded-Alumco transition-colors"
                    title="Cerrar sesi&oacute;n">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden relative">

        <!-- Header -->
        <header class="bg-Alumco-blue text-white py-4 px-8 shadow-sm flex justify-between items-center z-10 relative">
            <h1 class="text-2xl font-bold">@yield('title', 'Panel')</h1>
            <div class="text-sm">
                Hola, <span class="font-bold">{{ auth()->user()->name }}</span>
                @if(auth()->user()->hasAdminAccess())
                    <span class="ml-2 opacity-70 text-xs">&middot; Admin</span>
                @elseif(auth()->user()->isCapacitadorInterno())
                    <span class="ml-2 opacity-70 text-xs">&middot; Interno</span>
                @elseif(auth()->user()->isCapacitadorExterno())
                    <span class="ml-2 opacity-70 text-xs">&middot; Externo</span>
                @endif
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-8 relative pb-24 z-0">

            @if(session('success'))
                <div class="mb-6 px-4 py-3 bg-green-50 border-l-4 border-green-500 text-green-700 rounded shadow-sm flex flex-col" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm flex flex-col" role="alert">
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm" role="alert">
                    <p class="font-bold mb-1">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc ml-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <!-- Brand Watermark -->
            <div class="mt-8 flex justify-end items-center pointer-events-none w-full pb-8">
                <img src="{{ asset('images/logo/alumco-full.svg') }}" alt="Alumco" class="h-14 w-auto pointer-events-auto" dropzone="none">
            </div>

        </main>
    </div>

    <!-- Context Menu Markup -->
    @yield('context-menu')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contextMenu = document.getElementById('global-context-menu');

            if (!contextMenu) return;

            const hideMenu = () => { contextMenu.classList.remove('active'); };

            document.addEventListener('click', hideMenu);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideMenu(); });
            contextMenu.addEventListener('click', (e) => { e.stopPropagation(); });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>

</html>
