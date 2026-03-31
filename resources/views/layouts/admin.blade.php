<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumco - Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .rounded-Alumco { border-radius: 8px; } /* 8pt radius */

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

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(94, 94, 94, 0.35);
            border-radius: 9999px;
        }

        /* Context Menu Styles */
        .context-menu {
            position: absolute;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(94, 94, 94, 0.2);
            z-index: 50;
            min-w-max: 160px;
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

        @stack('styles')
    </style>
</head>

<body class="bg-Alumco-cream font-sans leading-normal tracking-normal text-Alumco-gray h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-20 bg-Alumco-blue flex flex-col items-center py-6 gap-8 z-10 shrink-0">
        <!-- Ícono de Reportes -->
        <a href="{{ route('admin.reportes.index') }}" class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Reportes">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </a>
        
        @if(auth()->user()->hasAdminAccess())
        <!-- Ícono de Admin Usuarios (Solo si tiene acceso admin) -->
        <a href="{{ route('admin.usuarios.index') }}" class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Usuarios">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </a>
        
        <!-- Ícono de Admin Config/Estamentos-Sedes -->
        <a href="#" class="text-white hover:text-Alumco-cyan cursor-pointer transition-colors" title="Ajustes (Estamentos/Sedes)">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
            @csrf
            <button type="submit" class="text-white hover:text-Alumco-cyan cursor-pointer bg-transparent border-none focus-ring p-1 rounded-Alumco transition-colors" title="Cerrar sesión">
                <!-- Icono de Logout -->
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </form>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <header class="bg-Alumco-blue text-white py-4 px-8 shadow-sm flex justify-between items-center z-10 relative">
            <h1 class="text-2xl font-bold">@yield('title', 'Panel de administración')</h1>
            <div class="text-sm">
                Hola, <span class="font-bold">{{ auth()->user()->name }}</span>
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
        // Utilidad global para menús contextuales
        document.addEventListener('DOMContentLoaded', function() {
            const contextMenu = document.getElementById('global-context-menu');
            
            if (!contextMenu) return;

            // Función para ocultar el menú
            const hideMenu = () => {
                contextMenu.classList.remove('active');
            };

            // Ocultar menu al hacer click en cualquier lado de la pantalla
            document.addEventListener('click', hideMenu);
            
            // Ocultar menu al apretar esc
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') hideMenu();
            });

            // Evitar que un click dentro del menú lo cierre (opcional, o manejar en los items)
            contextMenu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            // La lógica para abrirlo dependerá de cada vista y requiere atrapar el event.preventDefault() 
            // y posicionar el style.top y style.left del contextMenu. Se inyectará en @push('scripts').
        });
    </script>
    @stack('scripts')
</body>

</html>
