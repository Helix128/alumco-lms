@extends('layouts.user')

@section('title', 'Mis cursos — Alumco')

@section('content')

{{-- GREETING --}}
@php
    $firstName  = explode(' ', trim($user->name))[0];
    $esFemenino = ($user->sexo ?? 'M') === 'F';
@endphp
<div class="mb-6">
    <p class="text-Alumco-gray/55 text-sm font-medium">{{ $esFemenino ? 'Bienvenida de vuelta' : 'Bienvenido de vuelta' }}</p>
    <h1 class="font-display font-black text-Alumco-gray text-3xl leading-tight">
        Hola, {{ $firstName }}
    </h1>
</div>

{{-- CURSOS VIGENTES --}}
@if($vigentes->isNotEmpty())
    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-2">
        @foreach ($vigentes as $curso)
            @php $progreso = $curso->progreso_calculado; @endphp

            <a href="{{ route('cursos.show', $curso) }}"
               class="card-link bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100/80 block">

                {{-- IMAGEN --}}
                <div class="relative h-40 bg-Alumco-blue/10 overflow-hidden">
                    @if ($curso->imagen_portada)
                        <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                             alt="{{ $curso->titulo }}"
                             class="card-img-zoom w-full h-full object-cover">
                        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-Alumco-blue/20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L2 9l10 6 10-6-10-6zM2 14l10 6 10-6M2 19l10 6 10-6"/>
                            </svg>
                        </div>
                    @endif

                    @if ($progreso > 0)
                        <span class="absolute bottom-2.5 right-3 bg-black/50 backdrop-blur-sm
                                     text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            {{ $progreso }}%
                        </span>
                    @endif
                </div>

                {{-- CUERPO --}}
                <div class="p-4">
                    <p class="font-bold text-Alumco-gray text-base leading-snug">{{ $curso->titulo }}</p>
                    @if ($curso->descripcion)
                        <p class="text-sm text-Alumco-gray/55 mt-1 line-clamp-2 leading-relaxed">
                            {{ $curso->descripcion }}
                        </p>
                    @endif

                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs text-Alumco-gray/50 font-medium">Progreso</span>
                            <span class="text-xs font-bold text-Alumco-blue">{{ $progreso }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 bg-Alumco-blue"
                                 style="width: {{ $progreso }}%"></div>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@else
No tienes cursos activos.
@endif

{{-- CURSOS COMPLETADOS --}}
@if($completados->isNotEmpty())
    <details class="mt-6">
        <summary class="inline-flex items-center gap-2 px-4 py-2 bg-Alumco-green-vivid/10 text-Alumco-green-vivid
                        text-sm font-bold rounded-full cursor-pointer select-none hover:bg-Alumco-green-vivid/20 transition-colors list-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Ver completados ({{ $completados->count() }})
        </summary>

        <div class="flex flex-col gap-4 lg:grid lg:grid-cols-2 mt-4">
            @foreach ($completados as $curso)
                <a href="{{ route('cursos.show', $curso) }}"
                   class="card-link bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100/80 block">

                    <div class="relative h-40 bg-Alumco-blue/10 overflow-hidden">
                        @if ($curso->imagen_portada)
                            <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                 alt="{{ $curso->titulo }}"
                                 class="card-img-zoom w-full h-full object-cover">
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-Alumco-blue/20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 3L2 9l10 6 10-6-10-6zM2 14l10 6 10-6M2 19l10 6 10-6"/>
                                </svg>
                            </div>
                        @endif

                        <span class="absolute top-2.5 right-2.5 bg-Alumco-green-vivid text-white
                                     text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                            ¡Completado!
                        </span>
                    </div>

                    <div class="p-4">
                        <p class="font-bold text-Alumco-gray text-base leading-snug">{{ $curso->titulo }}</p>
                        @if ($curso->descripcion)
                            <p class="text-sm text-Alumco-gray/55 mt-1 line-clamp-2 leading-relaxed">
                                {{ $curso->descripcion }}
                            </p>
                        @endif

                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-Alumco-gray/50 font-medium">Progreso</span>
                                <span class="text-xs font-bold text-Alumco-green-vivid">100%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-Alumco-green-vivid" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </details>
@endif

{{-- EMPTY STATE --}}
@if($vigentes->isEmpty() && $completados->isEmpty())
    <div class="text-center py-20">
        <div class="mx-auto w-20 h-20 rounded-full bg-Alumco-blue/10 flex items-center justify-center mb-5">
            <svg class="w-10 h-10 text-Alumco-blue/30" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3L2 9l10 6 10-6-10-6zM2 14l10 6 10-6M2 19l10 6 10-6"/>
            </svg>
        </div>
        <p class="font-display font-bold text-Alumco-gray text-lg">Sin cursos asignados</p>
        <p class="text-Alumco-gray/55 text-sm mt-2 max-w-xs mx-auto leading-relaxed">
            Tu capacitador/a asignar&aacute; cursos para tu estamento pronto.
        </p>
    </div>
@endif

@endsection
