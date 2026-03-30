<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ONG Alumco</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        Alumco: {
                            blue: '#205099',
                            green: '#AFDD83',
                            coral: '#FF6364',
                            yellow: '#F8B606',
                            cyan: '#A5B6F5',
                            gray: '#5E5E5E',
                            cream: '#FFF8EB',
                        }
                    },
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                        display: ['Sora', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-Alumco-cream font-sans text-Alumco-gray antialiased">
    <div class="relative isolate min-h-screen overflow-hidden">
        <img
            src="{{ asset('images/undraw/clouds_top.svg') }}"
            alt=""
            aria-hidden="true"
            class="pointer-events-none absolute -top-10 right-0 z-0 w-[28rem] max-w-none opacity-95 sm:-top-16 sm:w-[34rem] lg:w-[48rem]"
        >

        <img
            src="{{ asset('images/undraw/clouds_bottom.svg') }}"
            alt=""
            aria-hidden="true"
            class="pointer-events-none absolute -bottom-8 left-0 z-0 w-[22rem] max-w-none sm:w-[28rem] lg:w-[36rem]"
        >

        <main class="relative z-10 mx-auto flex min-h-screen w-full max-w-[1280px] items-center px-4 py-8 sm:px-8 lg:px-10">
            <div class="grid w-full items-end gap-8 lg:grid-cols-[minmax(0,720px)_minmax(260px,1fr)] lg:gap-5">
                <section>
                    <div class="mx-auto mb-6 w-fit lg:mx-0 lg:mb-5">
                        <img
                            src="{{ asset('images/logo/alumco-full.svg') }}"
                            alt="Logo Alumco"
                            class="h-auto w-[15.5rem] sm:w-[17.5rem] lg:w-[19.5rem]"
                        >
                    </div>

                    <div class="overflow-hidden rounded-[10px] border border-slate-300 bg-Alumco-cream shadow-[0_16px_35px_-24px_rgba(32,80,153,0.8)]">
                        <div class="bg-Alumco-blue px-4 py-4 sm:px-6">
                            <h1 class="font-display text-3xl font-extrabold tracking-tight text-white sm:text-[2.2rem]">
                                Bienvenid&#64; al portal de capacitaci&oacute;n!
                            </h1>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="space-y-6 px-4 py-6 sm:px-8 sm:py-8 lg:px-8 lg:py-7" novalidate>
                            @csrf

                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <label for="email" class="text-2xl font-black leading-tight text-Alumco-gray sm:text-[2rem]">Correo electr&oacute;nico</label>
                                    <a href="mailto:soporte@alumco.org" class="text-xl font-bold leading-tight text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-Alumco-blue focus-visible:ring-offset-2 focus-visible:ring-offset-Alumco-cream">
                                        &iquest;Olvidaste tu correo electr&oacute;nico?
                                    </a>
                                </div>

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-Alumco-gray/90" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
                                            <path d="M3.5 6.5A2.5 2.5 0 0 1 6 4h12a2.5 2.5 0 0 1 2.5 2.5v11A2.5 2.5 0 0 1 18 20H6a2.5 2.5 0 0 1-2.5-2.5v-11Zm2.36-.5 6.14 5.02L18.14 6H5.86Zm12.64 2.5-5.5 4.49a1.6 1.6 0 0 1-2 0L5.5 8.5v9a.5.5 0 0 0 .5.5h12a.5.5 0 0 0 .5-.5v-9Z" />
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
                                        placeholder="Ingresa tu correo"
                                        class="h-14 w-full rounded-[8px] border border-Alumco-blue/50 bg-white/85 pl-14 pr-4 text-lg font-medium text-Alumco-gray outline-none transition placeholder:text-slate-400 focus:border-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/20 @error('email') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror"
                                    >
                                </div>

                                @error('email')
                                    <p class="text-sm font-semibold text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <label for="password" class="text-2xl font-black leading-tight text-Alumco-gray sm:text-[2rem]">Contrase&ntilde;a</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xl font-bold leading-tight text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-Alumco-blue focus-visible:ring-offset-2 focus-visible:ring-offset-Alumco-cream">
                                            &iquest;Olvidaste tu contrase&ntilde;a?
                                        </a>
                                    @else
                                        <span class="text-xl font-bold leading-tight text-Alumco-blue">&iquest;Olvidaste tu contrase&ntilde;a?</span>
                                    @endif
                                </div>

                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-Alumco-gray/90" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
                                            <path d="M14.5 2a5.5 5.5 0 0 1 4.96 7.87l2.9 2.9a1 1 0 0 1 0 1.42l-1.58 1.58a1 1 0 0 1-1.42 0l-.37-.37-.84.84a1 1 0 0 1-1.42 0l-.84-.84-.8.8a1 1 0 0 1-1.1.21l-1.35-.54-4.92 4.92a1 1 0 0 1-.7.3H3a1 1 0 0 1-1-1v-3.04a1 1 0 0 1 .3-.7l7.07-7.07A5.5 5.5 0 1 1 14.5 2Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" />
                                        </svg>
                                    </span>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Ingresa tu contrase&ntilde;a"
                                        class="h-14 w-full rounded-[8px] border border-Alumco-blue/50 bg-white/85 pl-14 pr-4 text-lg font-medium text-Alumco-gray outline-none transition placeholder:text-slate-400 focus:border-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/20 @error('password') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror"
                                    >
                                </div>

                                @error('password')
                                    <p class="text-sm font-semibold text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-base font-semibold text-Alumco-gray">
                                    <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-Alumco-blue accent-Alumco-blue focus:ring-Alumco-blue">
                                    Recu&eacute;rdame
                                </label>
                            </div>

                            <button type="submit" class="mt-1 h-16 w-full rounded-[8px] bg-Alumco-blue px-6 text-[clamp(2.1rem,3.2vw,2.95rem)] font-black leading-none text-white shadow-[0_6px_0_0_rgba(13,52,110,1)] transition hover:bg-[#1B4687] focus:outline-none focus-visible:ring-4 focus-visible:ring-Alumco-blue/35">
                                Acceder
                            </button>

                            <div class="space-y-1 pt-2 text-center">
                                <p class="text-xl font-bold text-Alumco-gray">&iquest;No tienes una cuenta?</p>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-block text-[2rem] font-black leading-tight text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-Alumco-blue focus-visible:ring-offset-2 focus-visible:ring-offset-Alumco-cream">&iexcl;Reg&iacute;strate aqu&iacute;!</a>
                                @else
                                    <a href="#" class="inline-block text-[2rem] font-black leading-tight text-Alumco-blue transition hover:text-Alumco-coral focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-Alumco-blue focus-visible:ring-offset-2 focus-visible:ring-offset-Alumco-cream">&iexcl;Reg&iacute;strate aqu&iacute;!</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </section>

                <aside class="relative hidden h-full min-h-[560px] items-end justify-end lg:flex" aria-hidden="true">
                    <img
                        src="{{ asset('images/undraw/door_knock.svg') }}"
                        alt=""
                        class="h-auto w-full max-w-[25rem] object-contain"
                    >
                </aside>
            </div>
        </main>

        <footer class="relative z-10 pb-4 text-center text-sm font-medium text-Alumco-gray/70 sm:pb-6 lg:pb-8">
            &copy; {{ date('Y') }} ONG Alumco. Todos los derechos reservados.
        </footer>
    </div>
</body>
</html>
