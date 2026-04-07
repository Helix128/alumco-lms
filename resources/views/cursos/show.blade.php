@extends('layouts.user')

@section('title', $curso->titulo . ' — Alumco')

@section('course-banner')
    <div class="relative overflow-hidden">
        @if ($curso->imagen_portada)
            <div class="absolute inset-0 bg-cover bg-center blur-sm scale-105"
                 style="background-image: url('{{ asset('storage/' . $curso->imagen_portada) }}')"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        @endif

        {{-- Barra coral con back link + título + progreso --}}
        <div class="relative z-10 bg-Alumco-coral/95 px-5 pt-2 pb-3 text-white text-center">
            <a href="{{ route('cursos.index') }}"
               class="back-link inline-flex items-center gap-1 text-white/75 text-xs font-semibold mb-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Mis cursos
            </a>
            <p class="font-bold text-lg leading-tight">{{ $curso->titulo }}</p>
            <div class="flex items-center justify-center gap-2 mt-1.5 max-w-xs mx-auto">
                <span class="text-base font-black shrink-0">{{ $progreso }}%</span>
                <div class="flex-1 bg-white/30 rounded-full h-3">
                    <div class="h-3 bg-Alumco-green-vivid rounded-full transition-all duration-500"
                         style="width: {{ $progreso }}%"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

{{-- Encabezado de sección --}}
<div class="flex items-center justify-between mb-5">
    <h2 class="font-display font-black text-Alumco-gray text-xl">Módulos</h2>
    <span class="text-sm text-Alumco-gray/55 font-medium">
        {{ $curso->modulos->count() }}
        {{ $curso->modulos->count() === 1 ? 'módulo' : 'módulos' }}
    </span>
</div>

<div class="flex flex-col gap-3 lg:grid lg:grid-cols-2 lg:gap-4">
    @foreach ($curso->modulos as $index => $modulo)
        @php
            $completado  = $modulo->estaCompletadoPor(auth()->user());
            $accesible   = $modulo->estaAccesiblePara(auth()->user(), $curso);
            $tipoLabel   = \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido;
        @endphp

        {{-- VERDE: módulo completado --}}
        @if ($completado)
            <a href="{{ route('modulos.show', [$curso, $modulo]) }}"
               class="card-link flex items-center gap-4 bg-Alumco-green-vivid rounded-2xl p-4 shadow-sm">
                <div class="w-9 h-9 rounded-full bg-white/30 flex items-center justify-center
                            text-white font-black text-sm shrink-0">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-base leading-snug truncate">{{ $modulo->titulo }}</p>
                    <p class="text-white/70 text-xs mt-0.5 capitalize">{{ $tipoLabel }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white shrink-0" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>

        {{-- AZUL: accesible (no completado) --}}
        @elseif ($accesible)
            <a href="{{ route('modulos.show', [$curso, $modulo]) }}"
               class="card-link flex items-center gap-4 bg-Alumco-blue rounded-2xl p-4 shadow-sm">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center
                            text-white font-black text-sm shrink-0">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white font-bold text-base leading-snug truncate">{{ $modulo->titulo }}</p>
                    <p class="text-white/70 text-xs mt-0.5 capitalize">{{ $tipoLabel }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white/70 shrink-0" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        {{-- BLANCO: bloqueado --}}
        @else
            <div class="flex items-center gap-4 bg-white border border-gray-200 rounded-2xl p-4
                        opacity-55 cursor-not-allowed">
                <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center
                            text-Alumco-gray/50 font-black text-sm shrink-0">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-Alumco-gray font-bold text-base leading-snug truncate">{{ $modulo->titulo }}</p>
                    <p class="text-Alumco-gray/50 text-xs mt-0.5 capitalize">{{ $tipoLabel }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-Alumco-gray/40 shrink-0"
                     fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3.1-9H8.9V6A3.1 3.1 0 0 1 15.1 6v2z"/>
                </svg>
            </div>
        @endif
    @endforeach

    @if ($curso->modulos->isEmpty())
        <div class="col-span-2 text-center py-10">
            <p class="text-Alumco-gray/60 font-medium">Este curso aún no tiene módulos.</p>
        </div>
    @endif
</div>

@endsection
