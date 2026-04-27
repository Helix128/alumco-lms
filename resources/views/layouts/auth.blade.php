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
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-Alumco-cream font-sans text-Alumco-gray antialiased">
    <div class="relative flex h-screen w-full flex-col overflow-hidden isolate">
        <!-- Nubes Superiores -->
        <div class="pointer-events-none absolute top-0 -right-10 z-0 select-none">
            <img src="{{ asset('images/undraw/clouds_top.svg') }}" alt="" class="animate-cloud h-auto w-[35vw] opacity-90 lg:w-[45vw]">
        </div>

        <!-- Nubes Inferiores -->
        <div class="pointer-events-none absolute bottom-0 -left-10 z-0 select-none">
            <img src="{{ asset('images/undraw/clouds_bottom.svg') }}" alt="" class="animate-cloud-slow h-auto w-[30vw] opacity-80 lg:w-[35vw]">
        </div>

        <main class="relative z-10 mx-auto flex h-full w-full max-w-[1440px] flex-1 items-center px-8 lg:px-20">
            <div class="grid w-full items-center gap-10 lg:grid-cols-[1.3fr_0.7fr]">

                <section class="mx-auto flex w-full max-w-3xl flex-col justify-center lg:mx-0">
                    <div class="mb-6 flex justify-start lg:mb-8">
                        <img
                            src="{{ asset('images/logo/alumco-full.svg') }}"
                            alt="Logo Alumco"
                            class="h-auto w-[16rem] sm:w-[18rem] lg:w-[22rem]"
                        >
                    </div>

                    @yield('content')
                </section>

                <!-- Ilustración -->
                <aside class="relative hidden h-full items-center justify-center lg:flex" aria-hidden="true">
                    <div class="flex w-full items-center justify-center lg:pt-24">
                        <img
                            src="{{ asset('images/undraw/door_knock.svg') }}"
                            alt=""
                            class="h-auto w-full max-w-[32rem] object-contain"
                        >
                    </div>
                </aside>
            </div>
        </main>

        <footer class="relative z-10 py-6 text-center text-xs font-bold uppercase tracking-widest text-Alumco-gray/40">
            &copy; {{ date('Y') }} Alumco
        </footer>
    </div>
</body>
</html>
