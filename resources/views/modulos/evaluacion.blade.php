@extends('layouts.user')

@section('title', 'Evaluación — ' . $curso->titulo . ' — Alumco')

@section('course-banner')
    <section class="border-b border-Alumco-blue/8 bg-gradient-to-br from-Alumco-blue/[0.04] via-white to-Alumco-cyan/[0.06] px-4 py-6 lg:px-12 lg:py-7">
        <div class="mx-auto max-w-[90rem]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('cursos.show', $curso) }}"
                       class="worker-focus mb-3 inline-flex items-center gap-2 rounded-full border border-Alumco-blue/15 bg-white/80 px-4 py-2 text-sm font-bold text-Alumco-blue shadow-sm transition-colors hover:border-Alumco-blue/30 hover:bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        {{ \Illuminate\Support\Str::limit($curso->titulo, 44) }}
                    </a>
                    <p class="mb-1 text-sm font-bold text-Alumco-blue/55">Evaluación del módulo</p>
                    <h1 class="font-display text-2xl font-black leading-tight tracking-tight text-Alumco-gray lg:text-3xl">
                        {{ $modulo->titulo }}
                    </h1>
                </div>

                <div class="flex shrink-0">
                    <span class="inline-flex items-center gap-2 rounded-full bg-Alumco-coral/10 px-4 py-2 text-sm font-black text-Alumco-coral-accessible">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        Una alternativa por pregunta
                    </span>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('bottom-nav')
    {{-- Navegación oculta para enfoque total en la evaluación --}}
@endsection

@section('content')
    <livewire:ver-evaluacion :modulo="$modulo" :curso="$curso" />
@endsection
