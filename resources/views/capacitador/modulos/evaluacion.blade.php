@extends('layouts.panel')

@section('title', 'Editar evaluación')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-sm text-Alumco-blue hover:underline mb-6 inline-block">
            ← Volver a {{ $curso->titulo }}
        </a>

        <h2 class="text-2xl font-bold text-Alumco-gray mb-6">Editar evaluación: {{ $modulo->titulo }}</h2>

        @livewire('capacitador.editar-evaluacion',
            ['evaluacion' => $modulo->evaluacion, 'curso' => $curso],
            key('eval-' . $modulo->id))
    </div>
@endsection
