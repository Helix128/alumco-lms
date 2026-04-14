@extends('layouts.user')

@section('title', 'Mis cursos — Alumco')

@section('content')

{{-- GREETING --}}
@php $firstName = explode(' ', trim($user->name))[0]; @endphp
<div class="mb-6">
    <p class="text-Alumco-gray/55 text-sm font-medium">Bienvenido de vuelta</p>
    <h1 class="font-display font-black text-Alumco-gray text-3xl leading-tight">
        Hola, {{ $firstName }}
    </h1>
</div>

{{-- TARJETAS DE CURSOS --}}
<div class="flex flex-col gap-4 lg:grid lg:grid-cols-2">
    @forelse ($cursos as $curso)
        @php $progreso = $curso->progresoParaUsuario($user); @endphp

        @php $estaBloqueado = now()->startOfDay() < $curso->fecha_inicio; @endphp

        <a href="{{ $estaBloqueado ? '#' : route('cursos.show', $curso) }}"
           {!! $estaBloqueado ? 'onclick="event.preventDefault()"' : '' !!}
           class="{{ $estaBloqueado ? 'opacity-80 cursor-not-allowed grayscale-[10%]' : 'card-link' }} bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100/80 block">

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

                {{-- Pill de progreso (si ya empezó) --}}
                @if ($progreso > 0 && $progreso < 100)
                    <span class="absolute bottom-2.5 right-3 bg-black/50 backdrop-blur-sm
                                 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                        {{ $progreso }}%
                    </span>
                @endif

                {{-- Badge "¡Completado!" --}}
                @if ($progreso === 100)
                    <span class="absolute top-2.5 right-2.5 bg-Alumco-green-vivid text-white
                                 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                        ¡Completado!
                    </span>
                @endif
            </div>

            {{-- CUERPO DE LA TARJETA --}}
            <div class="p-4">
                <p class="font-bold text-Alumco-gray text-base leading-snug">{{ $curso->titulo }}</p>
                @if ($curso->descripcion)
                    <p class="text-sm text-Alumco-gray/55 mt-1 line-clamp-2 leading-relaxed">
                        {{ $curso->descripcion }}
                    </p>
                @endif

                @if ($estaBloqueado)
                    <div class="mt-3 bg-gray-50 border border-gray-100 rounded-xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-Alumco-gray/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-xs text-Alumco-gray font-bold">Bloqueado</span>
                        </div>
                        <span class="text-xs text-Alumco-gray/60 font-medium">Inicia: {{ $curso->fecha_inicio->format('d/m/Y') }}</span>
                    </div>
                @else
                    {{-- BARRA DE PROGRESO --}}
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs text-Alumco-gray/50 font-medium">Progreso</span>
                            <span class="text-xs font-bold
                                {{ $progreso === 100 ? 'text-Alumco-green-vivid' : 'text-Alumco-blue' }}">
                                {{ $progreso }}%
                            </span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                        {{ $progreso === 100 ? 'bg-Alumco-green-vivid' : 'bg-Alumco-blue' }}"
                                 style="width: {{ $progreso }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

        </a>
    @empty
        <div class="col-span-2 text-center py-20">
            <div class="mx-auto w-20 h-20 rounded-full bg-Alumco-blue/10 flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-Alumco-blue/30" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L2 9l10 6 10-6-10-6zM2 14l10 6 10-6M2 19l10 6 10-6"/>
                </svg>
            </div>
            <p class="font-display font-bold text-Alumco-gray text-lg">Sin cursos asignados</p>
            <p class="text-Alumco-gray/55 text-sm mt-2 max-w-xs mx-auto leading-relaxed">
                Tu capacitador asignará cursos para tu estamento pronto.
            </p>
        </div>
    @endforelse
</div>

@endsection
