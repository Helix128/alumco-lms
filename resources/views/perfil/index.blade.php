@extends('layouts.user')
@section('title', 'Mi perfil — Alumco')

@section('content')

@php
    $initials = collect(explode(' ', trim($user->name)))
        ->map(fn($w) => strtoupper($w[0] ?? ''))
        ->take(2)
        ->join('');
@endphp

<div class="space-y-6">
    <section class="worker-card overflow-hidden">
        <div class="border-t-4 border-Alumco-blue p-5 lg:p-7">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="flex h-16 w-16 sm:h-20 sm:w-20 shrink-0 items-center justify-center rounded-full bg-Alumco-blue text-white shadow-inner">
                        <span class="font-display text-2xl sm:text-3xl font-black leading-none">{{ $initials }}</span>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-display text-2xl sm:text-3xl font-black leading-tight text-Alumco-gray truncate">{{ $user->name }}</h1>
                        <p class="mt-0.5 text-sm sm:text-base font-semibold text-Alumco-gray/70 truncate">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 sm:justify-end">
                    @if ($user->sede)
                        <span class="inline-flex items-center gap-2 rounded-full bg-Alumco-blue/10 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-Alumco-blue ring-1 ring-Alumco-blue/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/>
                            </svg>
                            {{ $user->sede->nombre }}
                        </span>
                    @endif
                    @if ($user->estamento)
                        <span class="rounded-full bg-Alumco-green/35 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-Alumco-gray ring-1 ring-Alumco-green/20">
                            {{ $user->estamento->nombre }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-3 gap-2.5 sm:gap-4">
        <div class="worker-card p-4 sm:p-5 text-center">
            <p class="font-display text-2xl sm:text-4xl font-black text-Alumco-blue">{{ $totalCursos }}</p>
            <p class="mt-0.5 text-[10px] sm:text-base font-bold text-Alumco-gray/70 uppercase sm:normal-case">Asignados</p>
        </div>
        <div class="worker-card p-4 sm:p-5 text-center">
            <p class="font-display text-2xl sm:text-4xl font-black text-Alumco-green-accessible">{{ $cursosCompletados }}</p>
            <p class="mt-0.5 text-[10px] sm:text-base font-bold text-Alumco-gray/70 uppercase sm:normal-case">Listos</p>
        </div>
        <div class="worker-card p-4 sm:p-5 text-center">
            <p class="font-display text-2xl sm:text-4xl font-black text-Alumco-gold-accessible">{{ $cursosEnProgreso }}</p>
            <p class="mt-0.5 text-[10px] sm:text-base font-bold text-Alumco-gray/70 uppercase sm:normal-case">En proceso</p>
        </div>
    </section>

    <section class="worker-card p-5 lg:p-6">
        <x-accessibility-preferences title="Preferencias de accesibilidad" description="Son las mismas opciones del botón Opciones" />
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section>
            <h2 class="mb-3 font-display text-xl font-black text-Alumco-gray">Información</h2>
            <div class="worker-card divide-y divide-gray-100">
                <div class="flex items-start gap-5 px-6 py-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-Alumco-gray/60">Correo electrónico</p>
                        <p class="mt-0.5 break-all text-base font-bold text-Alumco-gray">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-5 px-6 py-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-Alumco-gray/60">Fecha de nacimiento</p>
                        <p class="mt-0.5 text-base font-bold text-Alumco-gray">
                            {{ $user->fecha_nacimiento
                                ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y')
                                : '—' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-5 px-6 py-5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m3-3H15m-1.5 3H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-Alumco-gray/60">Sede</p>
                        <p class="mt-0.5 text-base font-bold text-Alumco-gray">
                            {{ $user->sede?->nombre ?? 'Sin sede asignada' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h2 class="mb-3 font-display text-xl font-black text-Alumco-gray">Certificados recientes</h2>
            @if ($certificados->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($certificados as $cert)
                        <div class="worker-card flex items-center gap-5 p-5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-Alumco-green/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-Alumco-green-accessible" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 1 9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1Z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-base font-black leading-snug text-Alumco-gray">{{ $cert->curso->titulo }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-Alumco-gray/60">
                                    {{ $cert->fecha_emision?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                            <a href="{{ route('mis-certificados.descargar', $cert) }}"
                               class="worker-focus rounded-full bg-Alumco-blue px-4 py-2.5 text-sm font-black text-white">
                                Descargar
                            </a>
                        </div>
                    @endforeach

                    @if ($certificados->count() >= 5)
                        <a href="{{ route('mis-certificados.index') }}"
                           class="worker-focus inline-flex rounded-full px-4 py-2 text-base font-bold text-Alumco-blue hover:bg-Alumco-blue/5">
                            Ver todos mis certificados
                        </a>
                    @endif
                </div>
            @else
                <div class="worker-card p-5 text-base font-semibold text-Alumco-gray/65">
                    Aún no tienes certificados recientes.
                </div>
            @endif
        </section>
    </div>

    <form action="{{ route('logout') }}" method="POST" class="max-w-sm">
        @csrf
        <button type="submit"
                class="btn-logout worker-focus w-full rounded-full border-2 border-Alumco-coral-accessible px-5 py-4 text-lg font-black text-Alumco-coral-accessible">
            Cerrar sesión
        </button>
    </form>
</div>

@endsection
