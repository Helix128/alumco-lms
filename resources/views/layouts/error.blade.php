<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') — Alumco</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-Alumco-cream font-sans text-Alumco-gray antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:py-16">

        <!-- Logo -->
        <a href="{{ route('login') }}" class="mb-8 block">
            <img
                src="{{ asset('images/logo/alumco-full.svg') }}"
                alt="Alumco"
                class="h-auto w-36 sm:w-48 md:w-56"
            >
        </a>

        <!-- Card -->
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">

            <!-- Card header -->
            <div class="bg-Alumco-blue px-6 py-5 sm:px-8 sm:py-6">
                <p class="font-display text-xs font-bold uppercase tracking-widest text-white/50 sm:text-sm">
                    @yield('code', 'Error')
                </p>
                <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    @yield('header-title', 'Algo salió mal')
                </h1>
            </div>

            <!-- Card body: content left, illustration right -->
            <div class="flex flex-col items-center gap-8 px-6 py-8 sm:px-8 sm:py-10 md:flex-row md:items-center md:gap-10">

                <!-- Error content -->
                <div class="flex-1 space-y-6">
                    @yield('content')
                </div>

                <!-- SVG illustration -->
                <div class="flex w-44 flex-shrink-0 items-center justify-center md:w-48">
                    @yield('illustration')
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-xs font-bold uppercase tracking-widest text-Alumco-gray/40">
            &copy; {{ date('Y') }} Alumco
        </footer>
    </div>
</body>
</html>
