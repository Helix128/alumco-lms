@extends('layouts.user')

@section('title', 'Evaluación — ' . $curso->titulo . ' — Alumco')

@section('course-banner')
    <div class="relative overflow-hidden">
        @if ($curso->imagen_portada)
            <div class="absolute inset-0 bg-cover bg-center blur-sm scale-105"
                 style="background-image: url('{{ asset('storage/' . $curso->imagen_portada) }}')"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        @endif

        <div class="relative z-10 bg-Alumco-coral/95 px-5 py-3 text-white text-center">
            <p class="text-sm opacity-90">{{ $curso->titulo }}</p>
            <p class="font-bold text-base leading-tight">{{ $modulo->titulo }}</p>
            <p class="font-black text-lg">Evaluación</p>
        </div>
    </div>
@endsection

{{-- Ya no ocultamos el nav por Blade, se ocultará en el componente por CSS dinámico --}}
@section('content')
    <livewire:ver-evaluacion :modulo="$modulo" :curso="$curso" />
@endsection
