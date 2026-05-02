@extends('layouts.user')

@section('title', $curso->titulo . ' — Alumco')

@section('course-banner')
    @php
        $accent = $curso->color_promedio ?? '#205099';
        $statusLabel = match(true) {
            $progreso == 0 => 'No iniciado',
            $progreso >= 100 => 'Completado',
            default => 'En proceso',
        };
        $statusClass = match(true) {
            $progreso == 0    => 'bg-gray-100 text-Alumco-gray/70 ring-1 ring-gray-200',
            $progreso >= 100  => 'bg-Alumco-green-accessible text-white shadow-sm',
            default           => 'bg-Alumco-blue text-white shadow-sm',
        };
        $statusIconPath = match(true) {
            $progreso >= 100 => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
            $progreso > 0    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
            default          => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        };
    @endphp

    <div class="w-full py-10 lg:py-16"
         style="--course-accent: {{ $accent }};
                background-color: color-mix(in srgb, {{ $accent }} 6%, #FDF9F3);
                border-bottom: 1px solid color-mix(in srgb, {{ $accent }} 18%, #e2e8f0);">

        <div class="mx-auto max-w-[90rem] px-5 lg:px-12">
            <div class="grid gap-8 lg:grid-cols-[1fr_360px] lg:items-center">
                <div class="order-2 lg:order-1">
                    <div class="mb-7 flex flex-wrap items-center gap-3">
                        <a href="{{ route('cursos.index') }}"
                           class="worker-focus group inline-flex items-center gap-2 rounded-full border border-Alumco-blue/20 bg-white px-4 py-2 text-sm font-bold text-Alumco-blue shadow-sm transition-colors hover:bg-Alumco-blue/5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Mis cursos
                        </a>
                        <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black {{ $statusClass }}">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                {!! $statusIconPath !!}
                            </svg>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <h1 class="font-display text-4xl font-black leading-[1.1] tracking-tight text-Alumco-gray lg:text-5xl">
                        {{ $curso->titulo }}
                    </h1>

                    @if ($curso->descripcion)
                        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-Alumco-gray/70 lg:text-xl">
                            {{ $curso->descripcion }}
                        </p>
                    @endif

                    <div class="mt-8 max-w-lg">
                        <div class="mb-2.5 flex items-center justify-between gap-3">
                            <span class="text-sm font-bold text-Alumco-gray/55">Progreso actual</span>
                            <span class="text-xl font-black text-Alumco-blue">{{ $progreso }}%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-black/8">
                            <div class="h-full rounded-full bg-Alumco-blue transition-all duration-1000 ease-out"
                                 style="width: {{ $progreso }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="relative aspect-video overflow-hidden rounded-3xl shadow-lg ring-1 ring-black/8 lg:aspect-square"
                         style="box-shadow: 0 16px 40px color-mix(in srgb, {{ $accent }} 20%, transparent);">
                        @if ($curso->imagen_portada)
                            <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                 alt="{{ $curso->titulo }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-Alumco-blue/8">
                                <svg class="h-24 w-24 text-Alumco-blue/20" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v-2.3l-7 3.85-7-3.85v-2.3Z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<section class="mx-auto max-w-5xl pb-16">
    <div class="mb-12 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="font-display text-3xl font-black text-Alumco-gray">Programa del curso</h2>
            <p class="text-lg font-medium text-Alumco-gray/65">Avanza etapa por etapa para completar tu capacitación.</p>
        </div>
        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-Alumco-blue/5 px-5 py-3 sm:mt-0">
            <span class="text-sm font-bold text-Alumco-blue">
                {{ $curso->modulos->count() }} {{ $curso->modulos->count() === 1 ? 'Actividad' : 'Actividades' }}
            </span>
            <span class="h-4 w-px bg-Alumco-blue/20"></span>
            <span class="text-sm font-bold text-Alumco-blue">
                {{ $curso->secciones->count() }} {{ $curso->secciones->count() === 1 ? 'Etapa' : 'Etapas' }}
            </span>
        </div>
    </div>

    @php
        $globalIndex = 0;
        $secciones = $curso->secciones;
        // Filtramos en memoria de la colección completa cargada
        $modulosSinSeccion = $curso->modulos->whereNull('seccion_id');
    @endphp

    <div class="relative space-y-16">
        {{-- Línea vertical del Timeline (solo visible si hay más de 1 módulo) --}}
        @if($curso->modulos->count() > 1)
            <div class="absolute left-6 top-12 bottom-12 w-0.5 bg-gray-200 lg:left-8" aria-hidden="true"></div>
        @endif

        {{-- Secciones y sus Módulos --}}
        @foreach ($secciones as $seccion)
            @php
                $modulosSeccion = $seccion->modulos;
                $completadosSeccion = $modulosSeccion->filter(fn($m) => $m->estaCompletadoPor(auth()->user()))->count();
                $totalSeccion = $modulosSeccion->count();
                $porcentajeSeccion = $totalSeccion > 0 ? round(($completadosSeccion / $totalSeccion) * 100) : 0;
            @endphp
            <div class="relative">
                {{-- Cabecera de Etapa --}}
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4 lg:gap-6">
                        <div class="z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-Alumco-blue text-white shadow-xl shadow-Alumco-blue/20 lg:h-16 lg:w-16">
                            <span class="font-display text-xl font-black lg:text-2xl">{{ $loop->iteration }}</span>
                        </div>
                        <div>
                            <h3 class="font-display text-xl font-black text-Alumco-gray lg:text-2xl">{{ $seccion->titulo }}</h3>
                            <div class="mt-1 flex items-center gap-3">
                                <span class="text-xs font-bold uppercase tracking-widest text-Alumco-gray/50">Etapa del curso</span>
                                <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                                <span class="text-xs font-black text-Alumco-blue">{{ $totalSeccion }} {{ $totalSeccion === 1 ? 'actividad' : 'actividades' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($totalSeccion > 0)
                        <div class="flex items-center gap-4 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-black/[0.03]">
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase tracking-wider text-Alumco-gray/40">Progreso</p>
                                <p class="text-sm font-black text-Alumco-green-accessible">{{ $porcentajeSeccion }}%</p>
                            </div>
                            <div class="relative h-12 w-12">
                                <svg class="h-full w-full" viewBox="0 0 36 36">
                                    <path class="text-gray-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="text-Alumco-green-accessible transition-all duration-1000" stroke-width="3" stroke-dasharray="{{ $porcentajeSeccion }}, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="ml-6 space-y-6 lg:ml-8">
                    @foreach ($modulosSeccion as $modulo)
                        @include('cursos.partials.modulo-timeline-card', [
                            'modulo' => $modulo,
                            'index' => ++$globalIndex,
                            'curso' => $curso
                        ])
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Módulos sin sección (si existen) --}}
        @if($modulosSinSeccion->isNotEmpty())
            <div class="relative">
                <div class="mb-8 flex items-center gap-4 lg:gap-6 opacity-60">
                    <div class="z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gray-200 text-Alumco-gray lg:h-16 lg:w-16">
                        <svg class="h-6 w-6 lg:h-8 lg:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 15.75h16.5m-16.5-7.5h16.5m-16.5-3.75h16.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-black text-Alumco-gray lg:text-2xl">Otros contenidos</h3>
                        <p class="text-xs font-bold uppercase tracking-widest text-Alumco-gray/50 mt-1">Módulos adicionales</p>
                    </div>
                </div>

                <div class="ml-6 space-y-6 lg:ml-8">
                    @foreach ($modulosSinSeccion as $modulo)
                        @include('cursos.partials.modulo-timeline-card', [
                            'modulo' => $modulo,
                            'index' => ++$globalIndex,
                            'curso' => $curso
                        ])
                    @endforeach
                </div>
            </div>
        @endif

        @if ($curso->modulos->isEmpty())
            <div class="worker-card px-5 py-14 text-center">
                <p class="text-lg font-bold text-Alumco-gray/70">Este curso aún no tiene módulos.</p>
            </div>
        @endif
    </div>
</section>

@endsection
