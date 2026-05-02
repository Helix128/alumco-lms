<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar certificado - Alumco</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="worker-shell min-h-screen font-sans text-Alumco-gray antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-3xl flex-col gap-6 px-4 py-8 sm:px-6 lg:py-12">
        <header class="flex items-center justify-between gap-4">
            <a href="{{ route('certificados.verificar.index') }}" class="worker-focus worker-pill inline-flex items-center gap-3">
                <img src="{{ asset('images/logo/alumco-full.svg') }}"
                     alt="Logo Alumco"
                     width="128"
                     height="34"
                     class="h-9 w-auto">
            </a>
        </header>

        <section class="worker-card rounded-[22px] p-6 sm:p-8">
            <div class="flex flex-col gap-2">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-Alumco-blue">Verificación pública</p>
                <h1 class="font-display text-3xl font-black text-Alumco-gray sm:text-4xl">Verificar certificado</h1>
                <p class="max-w-2xl text-base font-medium leading-relaxed text-Alumco-gray/70">
                    Ingresa el código del certificado o escanea el QR impreso en el documento.
                </p>
            </div>

            <form action="{{ route('certificados.verificar.index') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row">
                <label for="codigo" class="sr-only">Código de verificación</label>
                <input id="codigo"
                       name="codigo"
                       value="{{ $codigo }}"
                       type="text"
                       autocomplete="off"
                       placeholder="Código UUID del certificado"
                       class="worker-focus min-h-12 flex-1 rounded-2xl border border-Alumco-blue/15 bg-white px-4 text-sm font-bold text-Alumco-gray shadow-sm outline-none transition focus:border-Alumco-blue">
                <button type="submit"
                        class="worker-focus min-h-12 rounded-2xl bg-Alumco-blue px-6 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-Alumco-blue/15 transition hover:brightness-110 active:scale-[0.98]">
                    Buscar
                </button>
            </form>
        </section>

        @if ($wasSearched && $certificado)
            <section class="worker-card border-Alumco-green-accessible/25 p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-Alumco-green-accessible">Certificado válido</p>
                        <h2 class="mt-2 font-display text-2xl font-black text-Alumco-gray">Este certificado existe en Alumco LMS</h2>
                    </div>
                    <span class="worker-pill inline-flex w-fit items-center rounded-full bg-Alumco-green-accessible px-4 py-2 text-xs font-black uppercase tracking-wider text-white">
                        Validado
                    </span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-Alumco-gray/50">Participante</dt>
                        <dd class="mt-1 text-lg font-black text-Alumco-gray">{{ $certificado->user->name }}</dd>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-Alumco-gray/50">Curso</dt>
                        <dd class="mt-1 text-lg font-black text-Alumco-gray">{{ $certificado->curso->titulo }}</dd>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-Alumco-gray/50">Fecha de emisión</dt>
                        <dd class="mt-1 text-lg font-black text-Alumco-gray">{{ $certificado->fecha_emision?->format('d/m/Y') ?? 'No registrada' }}</dd>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <dt class="text-xs font-black uppercase tracking-wider text-Alumco-gray/50">Código</dt>
                        <dd class="mt-1 break-all font-mono text-sm font-bold text-Alumco-gray">{{ $certificado->codigo_verificacion }}</dd>
                    </div>
                </dl>

                <a href="{{ route('certificados.verificar.index') }}"
                   class="worker-focus mt-6 inline-flex min-h-12 items-center justify-center rounded-2xl border border-Alumco-blue/15 bg-white px-5 text-sm font-black uppercase tracking-widest text-Alumco-blue shadow-sm transition hover:border-Alumco-blue/35 hover:bg-Alumco-blue/5">
                    Buscar otro certificado
                </a>
            </section>
        @elseif ($wasSearched)
            <section class="worker-card border-Alumco-coral-accessible/20 p-6 sm:p-8">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-Alumco-coral-accessible">No validado</p>
                <h2 class="mt-2 font-display text-2xl font-black text-Alumco-gray">No encontramos un certificado con ese código</h2>
                <p class="mt-3 text-base font-medium leading-relaxed text-Alumco-gray/70">
                    Revisa que el código esté completo y vuelve a buscar. Si escaneaste un QR, intenta ingresar el código manualmente.
                </p>
            </section>
        @endif
    </main>
</body>
</html>
