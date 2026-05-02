@extends('layouts.user')

@section('title', 'Mis cursos — Alumco')

@section('content')

@php
    $firstName = explode(' ', trim($user->name))[0];
    $esFemenino = ($user->sexo ?? 'M') === 'F';
    $cursosEnProceso = $vigentes->filter(fn($curso) => ($curso->progreso_calculado ?? 0) > 0 && ($curso->progreso_calculado ?? 0) < 100)->count();
@endphp

<div class="space-y-10">
    <section class="worker-soft-panel p-6 ring-1 ring-white/80 lg:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-base font-semibold text-Alumco-blue">
                    {{ $esFemenino ? 'Bienvenida de vuelta' : 'Bienvenido de vuelta' }}
                </p>
                <h1 class="mt-1 font-display text-3xl font-black leading-tight text-Alumco-gray lg:text-4xl">
                    Hola, {{ $firstName }}
                </h1>
                <p class="mt-3 text-base leading-relaxed text-Alumco-gray/75 lg:text-lg">
                    Revisa tus cursos disponibles y continúa desde donde quedaste.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 lg:min-w-96 lg:gap-4">
                <div class="rounded-3xl bg-white/85 p-4 text-center shadow-sm ring-1 ring-Alumco-blue/10">
                    <p class="font-display text-3xl font-black text-Alumco-blue">{{ $vigentes->count() }}</p>
                    <p class="mt-1 text-sm font-bold text-Alumco-gray/70">Vigentes</p>
                </div>
                <div class="rounded-3xl bg-white/85 p-4 text-center shadow-sm ring-1 ring-Alumco-yellow/25">
                    <p class="font-display text-3xl font-black text-Alumco-gold-accessible">{{ $cursosEnProceso }}</p>
                    <p class="mt-1 text-sm font-bold text-Alumco-gray/70">En proceso</p>
                </div>
                <div class="rounded-3xl bg-white/85 p-4 text-center shadow-sm ring-1 ring-Alumco-green-accessible/20">
                    <p class="font-display text-3xl font-black text-Alumco-green-accessible">{{ $completados->count() }}</p>
                    <p class="mt-1 text-sm font-bold text-Alumco-gray/70">Completados</p>
                </div>
            </div>
        </div>
    </section>

    @if($vigentes->isNotEmpty())
        <section>
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-display text-2xl font-black text-Alumco-gray">Cursos vigentes</h2>
                    <p class="text-base font-medium text-Alumco-gray/65">Disponibles para realizar ahora.</p>
                </div>
                <span class="text-sm font-bold text-Alumco-blue">{{ $vigentes->count() }} {{ $vigentes->count() === 1 ? 'curso' : 'cursos' }}</span>
            </div>

            <div class="worker-course-grid grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($vigentes as $curso)
                    @php
                        $progreso = $curso->progreso_calculado ?? 0;
                        $accent = $curso->color_promedio ?? '#205099';
                        $ctaLabel = $progreso > 0 ? 'Continuar curso' : 'Comenzar curso';
                    @endphp

                    <a href="{{ route('cursos.show', $curso) }}"
                       class="worker-focus worker-course-tile group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100/80 transition-[transform,box-shadow] duration-200 hover:shadow-md hover:shadow-Alumco-blue/10">

                        {{-- Portada del curso --}}
                        <div class="worker-course-cover relative h-52 overflow-hidden rounded-t-3xl bg-Alumco-blue/5">
                            @if ($curso->imagen_portada)
                                <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                     alt="{{ $curso->titulo }}"
                                     class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-20 w-20 text-Alumco-blue/20" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-sm font-black text-Alumco-green-accessible shadow-sm backdrop-blur-sm">
                                    <span class="h-2 w-2 rounded-full bg-Alumco-green-accessible" aria-hidden="true"></span>
                                    Disponible
                                </span>
                                @if (isset($curso->is_preview))
                                    <span class="rounded-full bg-amber-500 px-3 py-1.5 text-sm font-black text-white shadow-sm">
                                        Vista previa
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Contenido de la tarjeta --}}
                        <div class="worker-course-body flex flex-1 flex-col p-6">
                            <h3 class="font-display text-xl font-black leading-snug text-Alumco-gray">
                                {{ $curso->titulo }}
                            </h3>
                            @if ($curso->descripcion)
                                <p class="mt-2 line-clamp-2 text-base leading-relaxed text-Alumco-gray/60">
                                    {{ $curso->descripcion }}
                                </p>
                            @endif

                            {{-- Barra de progreso --}}
                            <div class="mt-5">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-Alumco-gray/60">Tu progreso</span>
                                    <span class="text-sm font-black text-Alumco-blue">{{ $progreso }}%</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-Alumco-blue transition-all duration-700"
                                         style="width: {{ $progreso }}%"></div>
                                </div>
                            </div>

                            {{-- CTA botón --}}
                            <div class="mt-5">
                                <span class="flex w-full items-center justify-center gap-2 rounded-xl bg-Alumco-blue px-5 py-4 text-base font-black text-white shadow-sm transition-colors group-hover:bg-Alumco-blue/90">
                                    {{ $ctaLabel }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @elseif($completados->isEmpty())
        <section class="worker-card px-5 py-16 text-center">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-Alumco-blue/10">
                <svg class="h-10 w-10 text-Alumco-blue/50" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Z"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-black text-Alumco-gray">Sin cursos asignados</h2>
            <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-Alumco-gray/70">
                Tu capacitador/a asignará cursos para tu estamento pronto.
            </p>
        </section>
    @endif

    @if($completados->isNotEmpty())
        <section x-data="{ open: {{ $vigentes->isEmpty() ? 'true' : 'false' }} }">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-display text-2xl font-black text-Alumco-gray">Cursos completados</h2>
                    <p class="text-base font-medium text-Alumco-gray/65">Puedes revisarlos nuevamente cuando lo necesites.</p>
                </div>
                <button type="button"
                        x-on:click="open = !open"
                        class="worker-focus inline-flex items-center justify-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-black text-Alumco-green-accessible shadow-sm ring-1 ring-Alumco-green-accessible/20"
                        :aria-expanded="open.toString()">
                    <span>{{ $completados->count() }} {{ $completados->count() === 1 ? 'curso' : 'cursos' }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
            </div>

            <div x-cloak x-show="open" x-transition.opacity class="worker-course-grid grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($completados as $curso)
                    <a href="{{ route('cursos.show', $curso) }}"
                       class="worker-focus worker-course-tile group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100/80 transition-[transform,box-shadow] duration-200 hover:shadow-md hover:shadow-Alumco-green-accessible/10">

                        {{-- Portada --}}
                        <div class="worker-course-cover relative h-52 overflow-hidden rounded-t-3xl bg-Alumco-green-accessible/5">
                            @if ($curso->imagen_portada)
                                <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                     alt="{{ $curso->titulo }}"
                                     class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="h-20 w-20 text-Alumco-green-accessible/20" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-Alumco-green-accessible px-3 py-1.5 text-sm font-black text-white shadow-sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                    Completado
                                </span>
                            </div>
                        </div>

                        {{-- Contenido --}}
                        <div class="worker-course-body flex flex-1 flex-col p-6">
                            <h3 class="font-display text-xl font-black leading-snug text-Alumco-gray">
                                {{ $curso->titulo }}
                            </h3>
                            @if ($curso->descripcion)
                                <p class="mt-2 line-clamp-2 text-base leading-relaxed text-Alumco-gray/60">
                                    {{ $curso->descripcion }}
                                </p>
                            @endif

                            {{-- Progreso --}}
                            <div class="mt-5">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-Alumco-gray/60">Progreso</span>
                                    <span class="text-sm font-black text-Alumco-green-accessible">100%</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-Alumco-green-accessible" style="width: 100%"></div>
                                </div>
                            </div>

                            {{-- CTA revisión --}}
                            <div class="mt-5">
                                <span class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-Alumco-green-accessible/25 bg-white px-5 py-4 text-base font-black text-Alumco-green-accessible transition-colors group-hover:bg-Alumco-green-accessible/5">
                                    Ver de nuevo
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>

@endsection
