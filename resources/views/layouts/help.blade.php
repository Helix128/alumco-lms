<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Centro de ayuda') — Alumco</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/public.css', 'resources/js/app.js'])
</head>
<body class="help-shell min-h-screen bg-Alumco-cream font-sans text-Alumco-gray antialiased">
    @php
        $homeRoute = auth()->check()
            ? \App\Support\UserAreaRedirector::canonicalRouteName(auth()->user())
            : 'login';
    @endphp
    <header class="border-b border-Alumco-blue/10 bg-white">
        <div class="mx-auto flex min-h-20 max-w-6xl items-center justify-between gap-4 px-5 py-3">
            <a href="{{ route($homeRoute) }}" class="worker-focus inline-flex min-h-11 items-center" aria-label="Ir al inicio">
                <img src="{{ asset('images/logo/alumco-full.svg') }}" alt="Alumco" class="h-9 w-auto">
            </a>
            <nav class="flex items-center gap-2" aria-label="Navegación de ayuda">
                <a href="{{ route('help.index') }}" class="worker-focus inline-flex min-h-11 items-center rounded-xl px-4 text-sm font-black text-Alumco-blue" aria-current="{{ request()->routeIs('help.*') ? 'page' : 'false' }}">Centro de ayuda</a>
                <a href="{{ auth()->check() ? route('support.index') : route('support.public.create') }}" class="worker-focus inline-flex min-h-11 items-center rounded-xl bg-Alumco-blue px-4 text-sm font-black text-white">Soporte en línea</a>
            </nav>
        </div>
    </header>
    <main id="main-content" class="mx-auto w-full max-w-6xl px-5 py-8 lg:py-12">
        @yield('content')
    </main>
    <footer class="mx-auto max-w-6xl px-5 pb-10 text-sm font-medium text-gray-500">
        ¿No encontraste lo que necesitabas?
        <a class="font-bold text-Alumco-blue underline underline-offset-4" href="{{ auth()->check() ? route('support.index') : route('support.public.create') }}">Contacta a soporte</a>.
    </footer>
</body>
</html>
