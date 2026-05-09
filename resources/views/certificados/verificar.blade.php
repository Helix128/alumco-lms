<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar certificado - Alumco</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell min-h-screen font-sans text-Alumco-gray antialiased">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden isolate bg-gradient-to-br from-Alumco-cream via-Alumco-cream to-Alumco-cyan/20">
        <!-- Nubes Superiores -->
        <div class="pointer-events-none absolute top-0 -right-10 z-0 select-none">
            <img src="{{ asset('images/undraw/clouds_top.svg') }}" alt="" class="animate-cloud h-auto w-[35vw] opacity-90 lg:w-[45vw]">
        </div>

        <!-- Nubes Inferiores -->
        <div class="pointer-events-none absolute bottom-0 -left-10 z-0 select-none">
            <img src="{{ asset('images/undraw/clouds_bottom.svg') }}" alt="" class="animate-cloud-slow h-auto w-[30vw] opacity-80 lg:w-[35vw]">
        </div>

        <main class="relative z-10 mx-auto flex h-full w-full max-w-4xl flex-1 items-center px-6 py-12 lg:px-20">
            <div class="w-full space-y-8 animate-page-entry">
                <header class="flex justify-center lg:justify-start">
                    <a href="{{ route('certificados.verificar.index') }}" class="transition-transform hover:scale-105 active:scale-95">
                        <img src="{{ asset('images/logo/alumco-full.svg') }}"
                             alt="Logo Alumco"
                             class="h-10 w-auto lg:h-12">
                    </a>
                </header>

                <section class="overflow-hidden rounded-3xl border border-white/40 bg-white/80 shadow-2xl backdrop-blur-xl">
                    <div class="bg-Alumco-blue/90 px-8 py-6 lg:px-12">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-Alumco-cyan">Verificación pública</p>
                        <h1 class="mt-1 font-display text-2xl font-black text-white sm:text-3xl">Verificar certificado</h1>
                    </div>

                    <div class="px-8 py-8 lg:px-12 lg:py-10">
                        <p class="text-lg font-medium leading-relaxed text-Alumco-gray/70">
                            Ingresa el código del certificado o escanea el QR impreso en el documento para validar su autenticidad.
                        </p>

                        <form action="{{ route('certificados.verificar.index') }}" method="GET" class="mt-8 flex flex-col gap-4 sm:flex-row">
                            <label for="codigo" class="sr-only">Código de verificación</label>
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-5 flex items-center text-Alumco-gray/30" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input id="codigo"
                                       name="codigo"
                                       value="{{ $codigo }}"
                                       type="text"
                                       autocomplete="off"
                                       placeholder="Código UUID del certificado"
                                       class="h-14 w-full rounded-2xl border-2 border-slate-200/60 bg-white/50 pl-14 pr-4 text-lg font-bold text-Alumco-gray transition-all placeholder:text-slate-400 focus:border-Alumco-blue focus:bg-white focus:ring-4 focus:ring-Alumco-blue/10">
                            </div>
                            <button type="submit"
                                    class="group relative overflow-hidden rounded-2xl bg-Alumco-blue px-8 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:shadow-xl active:scale-95">
                                <span class="relative flex items-center gap-2">
                                    Buscar
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 transition-transform group-hover:translate-x-1">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                        </form>
                    </div>
                </section>

                @if ($wasSearched && $certificado)
                    <section class="overflow-hidden rounded-3xl border border-green-100 bg-green-50/50 p-8 shadow-xl backdrop-blur-sm ring-1 ring-green-200/50">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm font-black uppercase tracking-[0.22em] text-green-700">Certificado válido</p>
                                </div>
                                <h2 class="mt-3 font-display text-2xl font-black text-Alumco-gray">Este certificado es auténtico</h2>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-xs font-black uppercase tracking-wider text-white shadow-lg shadow-green-600/20">
                                Validado por Alumco
                            </span>
                        </div>

                        <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-green-200/50 bg-white/60 p-5 shadow-sm">
                                <dt class="text-xs font-black uppercase tracking-wider text-green-700/60">Participante</dt>
                                <dd class="mt-1 text-xl font-black text-Alumco-gray">{{ $certificado->user->name }}</dd>
                            </div>
                            <div class="rounded-2xl border border-green-200/50 bg-white/60 p-5 shadow-sm">
                                <dt class="text-xs font-black uppercase tracking-wider text-green-700/60">Curso</dt>
                                <dd class="mt-1 text-xl font-black text-Alumco-gray">{{ $certificado->curso->titulo }}</dd>
                            </div>
                            <div class="rounded-2xl border border-green-200/50 bg-white/60 p-5 shadow-sm">
                                <dt class="text-xs font-black uppercase tracking-wider text-green-700/60">Fecha de emisión</dt>
                                <dd class="mt-1 text-xl font-black text-Alumco-gray">{{ $certificado->fecha_emision?->format('d/m/Y') ?? 'No registrada' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-green-200/50 bg-white/60 p-5 shadow-sm">
                                <dt class="text-xs font-black uppercase tracking-wider text-green-700/60">Código de Verificación</dt>
                                <dd class="mt-1 break-all font-mono text-sm font-bold text-Alumco-gray">{{ $certificado->codigo_verificacion }}</dd>
                            </div>
                        </dl>

                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('certificados.verificar.index') }}"
                               class="text-sm font-bold text-Alumco-blue transition hover:text-Alumco-coral underline decoration-Alumco-blue/30 underline-offset-8">
                                Verificar otro documento
                            </a>
                        </div>
                    </section>
                @elseif ($wasSearched)
                    <section class="rounded-3xl border border-red-100 bg-red-50/50 p-8 shadow-xl backdrop-blur-sm ring-1 ring-red-200/50">
                        <div class="flex items-center gap-2">
                            <svg class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-red-700">No validado</p>
                        </div>
                        <h2 class="mt-3 font-display text-2xl font-black text-Alumco-gray">No encontramos un certificado con ese código</h2>
                        <p class="mt-4 text-lg font-medium leading-relaxed text-Alumco-gray/70">
                            Revisa que el código esté completo y vuelve a buscar. Si escaneaste un código QR, intenta ingresar el identificador manualmente.
                        </p>
                    </section>
                @endif
            </div>
        </main>

        <footer class="py-8 text-center text-xs font-bold uppercase tracking-widest text-Alumco-gray/30">
            &copy; {{ date('Y') }} Alumco &bull; Sistema de Gestión de Capacitación
        </footer>
    </div>
</body>
</html>
