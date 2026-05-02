@extends('layouts.user')
@section('title', 'Mis certificados — Alumco')

@section('content')

<div class="space-y-6">
    <section class="worker-card p-5 lg:p-7">
        <h1 class="font-display text-3xl font-black text-Alumco-gray">Mis certificados</h1>
        <p class="mt-2 max-w-2xl text-base leading-relaxed text-Alumco-gray/70 lg:text-lg">
            Aquí encontrarás los certificados obtenidos al completar cursos.
        </p>
    </section>

    @forelse ($certificados as $cert)
        <div class="worker-card flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-Alumco-green/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-Alumco-green-accessible" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 1 9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1Z"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-lg font-black leading-snug text-Alumco-gray">{{ $cert->curso->titulo }}</p>
                <p class="mt-1 text-base font-semibold text-Alumco-gray/65">
                    Emitido el {{ $cert->fecha_emision?->format('d/m/Y') ?? '—' }}
                </p>
            </div>

            <a href="{{ route('mis-certificados.descargar', $cert) }}"
               class="btn-download worker-focus inline-flex justify-center rounded-full bg-Alumco-blue px-5 py-3 text-base font-black text-white">
                Descargar
            </a>
        </div>

    @empty
        <section class="worker-card px-5 py-16 text-center">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-Alumco-blue/10">
                <svg class="h-10 w-10 text-Alumco-blue/45" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 1 9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1Z"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-black text-Alumco-gray">Aún no tienes certificados</h2>
            <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-Alumco-gray/70">
                Completa un curso al 100% para obtener tu primer certificado.
            </p>
            <a href="{{ route('cursos.index') }}"
               class="btn-primary worker-focus mt-6 inline-flex rounded-full bg-Alumco-blue px-8 py-4 text-lg font-black text-white shadow-sm">
                Ir a mis cursos
            </a>
        </section>
    @endforelse
</div>

@endsection
