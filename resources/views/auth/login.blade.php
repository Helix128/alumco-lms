<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumco</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
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
            <div class="grid w-full items-stretch gap-10 lg:grid-cols-[1.35fr_0.65fr]">
                
                <!-- Sección Login -->
                <section class="flex flex-col justify-center w-full">
                    <div class="mb-6 flex justify-start lg:mb-8">
                        <img
                            src="{{ asset('images/logo/alumco-full.svg') }}"
                            alt="Logo Alumco"
                            class="h-auto w-[16rem] sm:w-[18rem] lg:w-[22rem]"
                        >
                    </div>

                    <div class="overflow-hidden rounded-xl border-2 border-slate-200 bg-white shadow-sm">
                        <div class="bg-Alumco-blue px-8 py-4 lg:py-5">
                            <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl lg:text-4xl">
                                ¡Bienvenid@ al portal de capacitación!
                            </h1>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="space-y-6 px-8 py-8 lg:px-12 lg:py-10" novalidate>
                            @csrf

                            <!-- Correo -->
                            <div class="space-y-2">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                    <label for="email" class="text-2xl font-bold text-Alumco-gray lg:text-3xl">Correo electrónico</label>
                                    <a href="mailto:soporte@alumco.org" class="text-base font-bold text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none">
                                        ¿Olvidaste tu correo electrónico?
                                    </a>
                                </div>

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/40" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
                                            <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a1.75 1.75 0 01-1.644 0L1.5 8.67z" />
                                            <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a.25.25 0 00.286 0L22.5 6.908z" />
                                        </svg>
                                    </span>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="ejemplo@correo.com"
                                        class="h-14 w-full rounded-xl border-2 border-slate-200 bg-slate-50/30 pl-14 pr-4 text-xl font-medium text-Alumco-gray transition placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-0 @error('email') border-red-500 @enderror"
                                    >
                                </div>
                                @error('email')
                                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contraseña -->
                            <div class="space-y-2">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                                    <label for="password" class="text-2xl font-bold text-Alumco-gray lg:text-3xl">Contraseña</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-base font-bold text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    @endif
                                </div>

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/40" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
                                            <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="h-14 w-full rounded-xl border-2 border-slate-200 bg-slate-50/30 pl-14 pr-4 text-xl font-medium text-Alumco-gray transition placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-0 @error('password') border-red-500 @enderror"
                                    >
                                </div>
                                @error('password')
                                    <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botón Acceder -->
                            <div class="flex flex-col items-center gap-4 pt-2">
                                <button type="submit" class="flex h-16 w-full max-w-sm cursor-pointer items-center justify-center rounded-xl bg-Alumco-blue px-8 text-3xl font-bold text-white shadow-[0_6px_0_0_#163a71] transition-all hover:translate-y-[2px] hover:shadow-[0_4px_0_0_#163a71] active:translate-y-[6px] active:shadow-none focus:outline-none">
                                    Acceder
                                </button>
                                
                                <div class="text-center">
                                    <p class="text-lg font-bold text-Alumco-gray/60">¿No tienes una cuenta?</p>
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="inline-block text-3xl font-bold text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none">
                                        ¡Regístrate aquí!
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Ilustración -->
                <aside class="relative hidden lg:flex items-center justify-end" aria-hidden="true">
                    <div class="h-full flex items-end pb-32">
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
