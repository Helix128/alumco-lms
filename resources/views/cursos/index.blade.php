@extends('layouts.user')

@section('title', 'Mis cursos — Alumco')

@section('content')

@php
    $firstName = explode(' ', trim($user->name))[0];
    $esFemenino = ($user->sexo ?? 'M') === 'F';
    $cursosEnProceso = $vigentes->filter(fn($curso) => ($curso->progreso_calculado ?? 0) > 0 && ($curso->progreso_calculado ?? 0) < 100)->count();
@endphp

<div class="space-y-10" x-data="{ tab: 'disponibles' }">
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
                    Gestiona tu aprendizaje y revisa tus cursos asignados.
                </p>
            </div>

            <div class="grid grid-cols-4 gap-2.5 lg:min-w-[480px] lg:gap-4">
                <div class="rounded-3xl bg-white/85 p-3 text-center shadow-sm ring-1 ring-Alumco-blue/10 lg:p-4">
                    <p class="font-display text-2xl font-black text-Alumco-blue lg:text-3xl">{{ $vigentes->count() }}</p>
                    <p class="mt-0.5 text-[10px] font-bold text-Alumco-gray/70 uppercase lg:text-xs">Vigentes</p>
                </div>
                <div class="rounded-3xl bg-white/85 p-3 text-center shadow-sm ring-1 ring-Alumco-yellow/25 lg:p-4">
                    <p class="font-display text-2xl font-black text-Alumco-gold-accessible lg:text-3xl">{{ $cursosEnProceso }}</p>
                    <p class="mt-0.5 text-[10px] font-bold text-Alumco-gray/70 uppercase lg:text-xs">En proceso</p>
                </div>
                <div class="rounded-3xl bg-white/85 p-3 text-center shadow-sm ring-1 ring-Alumco-green-accessible/20 lg:p-4">
                    <p class="font-display text-2xl font-black text-Alumco-green-accessible lg:text-3xl">{{ $completados->count() }}</p>
                    <p class="mt-0.5 text-[10px] font-bold text-Alumco-gray/70 uppercase lg:text-xs">Completos</p>
                </div>
                <div class="rounded-3xl bg-white/85 p-3 text-center shadow-sm ring-1 ring-Alumco-gray/10 lg:p-4">
                    <p class="font-display text-2xl font-black text-Alumco-gray/50 lg:text-3xl">{{ $anteriores->count() }}</p>
                    <p class="mt-0.5 text-[10px] font-bold text-Alumco-gray/70 uppercase lg:text-xs">Pasados</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Navegación de Pestañas --}}
    <div class="flex items-center gap-2 border-b border-gray-100 pb-1">
        <button @click="tab = 'disponibles'" 
                :class="tab === 'disponibles' ? 'border-Alumco-blue text-Alumco-blue' : 'border-transparent text-Alumco-gray/50 hover:text-Alumco-gray hover:border-gray-200'"
                class="worker-focus relative px-6 py-4 text-sm font-black uppercase tracking-widest transition-all border-b-4 -mb-[2px]">
            Cursos Disponibles
        </button>
        <button @click="tab = 'historial'" 
                :class="tab === 'historial' ? 'border-Alumco-blue text-Alumco-blue' : 'border-transparent text-Alumco-gray/50 hover:text-Alumco-gray hover:border-gray-200'"
                class="worker-focus relative px-6 py-4 text-sm font-black uppercase tracking-widest transition-all border-b-4 -mb-[2px]">
            Historial de Cursos
        </button>
    </div>

    {{-- Contenedor de Contenido con Stack de Grid para Transición Fluida --}}
    <div class="grid">
        {{-- Contenido Pestaña: Disponibles --}}
        <div x-show="tab === 'disponibles'" 
             x-transition:enter="transition ease-out duration-150 delay-150" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="[grid-area:1/1] space-y-12">
            @if($vigentes->isNotEmpty())
                <section>
                    <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="font-display text-2xl font-black text-Alumco-gray">Cursos vigentes</h2>
                            <p class="text-base font-medium text-Alumco-gray/65">Disponibles para realizar ahora.</p>
                        </div>
                    </div>

                    <div class="worker-course-grid grid grid-cols-1 gap-6 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($vigentes as $curso)
                            @php
                                $progreso = $curso->progreso_calculado ?? 0;
                                $ctaLabel = $progreso > 0 ? 'Continuar curso' : 'Comenzar curso';
                            @endphp

                            <a href="{{ route('cursos.show', $curso) }}"
                               wire:navigate
                               class="worker-focus worker-course-tile group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100/80 transition-all hover:shadow-xl hover:shadow-Alumco-blue/5 hover:-translate-y-1">

                                <div class="worker-course-cover relative h-52 overflow-hidden rounded-t-3xl bg-Alumco-blue/5">
                                    @if ($curso->imagen_portada)
                                        <img src="{{ asset('storage/' . $curso->imagen_portada) }}" alt="{{ $curso->titulo }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-20 w-20 text-Alumco-blue/20" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v-2.3l-7 3.85-7-3.85v-2.3Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-sm font-black text-Alumco-green-accessible shadow-sm backdrop-blur-sm">
                                            <span class="h-2 w-2 rounded-full bg-Alumco-green-accessible" aria-hidden="true"></span>
                                            Vigente
                                        </span>
                                        @if (isset($curso->is_preview))
                                            <span class="rounded-full bg-amber-500 px-3 py-1.5 text-sm font-black text-white shadow-sm">
                                                Vista previa
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="worker-course-body flex flex-1 flex-col p-6">
                                    <h3 class="font-display text-xl font-black leading-tight text-Alumco-gray group-hover:text-Alumco-blue transition-colors">
                                        {{ $curso->titulo }}
                                    </h3>
                                    
                                    <div class="mt-5">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <span class="text-xs font-bold text-Alumco-gray/60 uppercase">Tu progreso</span>
                                            <span class="text-sm font-black text-Alumco-blue">{{ $progreso }}%</span>
                                        </div>
                                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full bg-Alumco-blue transition-all duration-700" style="width: {{ $progreso }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <span class="flex w-full items-center justify-center gap-2 rounded-xl bg-Alumco-blue px-5 py-4 text-base font-black text-white shadow-lg shadow-Alumco-blue/15 transition-all group-hover:brightness-110">
                                            {{ $ctaLabel }}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
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
                <section class="worker-card px-5 py-20 text-center">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-Alumco-blue/10">
                        <svg class="h-10 w-10 text-Alumco-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h2 class="font-display text-2xl font-black text-Alumco-gray">Sin cursos vigentes</h2>
                    <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-Alumco-gray/65">
                        No tienes cursos activos en este momento. Revisa el historial para ver tus capacitaciones pasadas.
                    </p>
                </section>
            @endif

            @if($completados->isNotEmpty())
                <section x-data="{ open: true }">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="font-display text-2xl font-black text-Alumco-gray">Cursos completados</h2>
                            <p class="text-base font-medium text-Alumco-gray/65">Puedes revisarlos nuevamente cuando lo necesites.</p>
                        </div>
                        <button type="button" @click="open = !open"
                                class="worker-focus flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-black text-Alumco-green-accessible shadow-sm ring-1 ring-Alumco-green-accessible/20">
                            <span>{{ $completados->count() }} completados</span>
                            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="open" x-transition.opacity class="worker-course-grid grid grid-cols-1 gap-6 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($completados as $curso)
                            <a href="{{ route('cursos.show', $curso) }}"
                               wire:navigate
                               class="worker-focus worker-course-tile group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100/80 transition-all hover:shadow-xl hover:shadow-Alumco-green-accessible/5">
                                
                                <div class="worker-course-cover relative h-52 overflow-hidden rounded-t-3xl bg-Alumco-green-accessible/5">
                                    @if ($curso->imagen_portada)
                                        <img src="{{ asset('storage/' . $curso->imagen_portada) }}" alt="{{ $curso->titulo }}" class="h-full w-full object-cover opacity-90 transition-transform duration-500 group-hover:scale-105 group-hover:opacity-100">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-20 w-20 text-Alumco-green-accessible/20" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v-2.3l-7 3.85-7-3.85v-2.3Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-4 left-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-Alumco-green-accessible px-3 py-1.5 text-sm font-black text-white shadow-sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                            Completado
                                        </span>
                                    </div>
                                </div>

                                <div class="worker-course-body flex flex-1 flex-col p-6">
                                    <h3 class="font-display text-xl font-black leading-tight text-Alumco-gray group-hover:text-Alumco-green-accessible transition-colors">
                                        {{ $curso->titulo }}
                                    </h3>
                                    <div class="mt-6 flex flex-1 flex-col justify-end">
                                        <span class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-Alumco-green-accessible/25 bg-white px-5 py-4 text-base font-black text-Alumco-green-accessible transition-all group-hover:bg-Alumco-green-accessible/5">
                                            Revisar curso
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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

        {{-- Contenido Pestaña: Historial --}}
        <div x-cloak x-show="tab === 'historial'" 
             x-transition:enter="transition ease-out duration-150 delay-150" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="[grid-area:1/1] space-y-12">
            <section>
                <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="font-display text-2xl font-black text-Alumco-gray">Historial de cursos</h2>
                        <p class="text-base font-medium text-Alumco-gray/65">Cursos cuyo periodo de disponibilidad ya finalizó.</p>
                    </div>
                </div>

                @if($anteriores->isNotEmpty())
                    <div class="worker-course-grid grid grid-cols-1 gap-6 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($anteriores as $curso)
                            @php
                                $progreso = $curso->progreso_calculado ?? 0;
                                $expiracion = $curso->planificaciones->max('fecha_fin');
                            @endphp

                            <div class="group relative flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-100/80 grayscale-[0.8] hover:grayscale-0 transition-all duration-500 opacity-75 hover:opacity-100 worker-course-tile">
                                {{-- Portada con Overlay de "Cerrado" --}}
                                <div class="worker-course-cover relative h-44 overflow-hidden bg-gray-100">
                                    @if ($curso->imagen_portada)
                                        <img src="{{ asset('storage/' . $curso->imagen_portada) }}" alt="{{ $curso->titulo }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-gray-200" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 3 2 8.5 12 14l10-5.5L12 3Zm-7 9.2 7 3.85 7-3.85v2.3l-7 3.85-7-3.85v-2.3Zm0 5 7 3.85 7-3.85v-2.3l-7 3.85-7-3.85v-2.3Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    <div class="absolute inset-0 bg-gray-900/10 backdrop-blur-[1px]"></div>
                                    
                                    <div class="absolute top-4 left-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-xs font-black text-Alumco-gray uppercase tracking-wider shadow-sm backdrop-blur-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>
                                            Pasado
                                        </span>
                                    </div>
                                </div>

                                {{-- Contenido --}}
                                <div class="worker-course-body flex flex-1 flex-col p-6">
                                    <h3 class="font-display text-lg font-black leading-snug text-Alumco-gray/80 group-hover:text-Alumco-gray transition-colors">
                                        {{ $curso->titulo }}
                                    </h3>
                                    
                                    <div class="mt-4 flex items-center gap-2 text-[11px] font-bold text-Alumco-gray/50 uppercase tracking-tight">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008H15v-.008ZM15 15h.008v.008H15V15Zm0 3h.008v.008H15V18Z" />
                                        </svg>
                                        Expiró el {{ $expiracion->format('d/m/Y') }}
                                    </div>

                                    <div class="mt-5">
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <span class="text-[10px] font-bold text-Alumco-gray/40 uppercase">Estatus final</span>
                                            <span class="text-sm font-black text-Alumco-gray/60">{{ $progreso }}%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-50">
                                            <div class="h-full rounded-full bg-Alumco-gray/20 transition-all duration-1000" style="width: {{ $progreso }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mt-8 flex flex-col gap-2">
                                        @if(isset($certificadosMap) && $certificadosMap->has($curso->id))
                                            <a href="{{ route('mis-certificados.descargar', $certificadosMap[$curso->id]) }}" 
                                               class="flex w-full items-center justify-center gap-2 rounded-xl bg-Alumco-green-accessible/10 px-4 py-3.5 text-sm font-black text-Alumco-green-accessible transition-all hover:bg-Alumco-green-accessible/20">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 3v13.5" />
                                                </svg>
                                                Descargar Certificado
                                            </a>
                                        @else
                                            <div class="flex w-full items-center justify-between rounded-xl bg-gray-50/50 px-4 py-3.5 ring-1 ring-inset ring-gray-100">
                                                <span class="text-xs font-black uppercase tracking-widest text-Alumco-gray/30">Acceso cerrado</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <section class="worker-card px-5 py-20 text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-100">
                            <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h2 class="font-display text-2xl font-black text-Alumco-gray">Historial vacío</h2>
                        <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-Alumco-gray/65">
                            Aún no tienes cursos que hayan finalizado su periodo de vigencia.
                        </p>
                    </section>
                @endif
            </section>
        </div>
    </div>
</div>

@endsection
