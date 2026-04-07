@extends('layouts.user')
@section('title', 'Mis certificados — Alumco')

@section('content')

<h1 class="font-display font-black text-Alumco-gray text-2xl mb-5">Mis certificados</h1>

@forelse ($certificados as $cert)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 mb-3">

        {{-- Ícono --}}
        <div class="w-12 h-12 rounded-full bg-Alumco-green/40 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-Alumco-green-vivid" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 1L9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1z"/>
            </svg>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <p class="font-bold text-Alumco-gray leading-snug truncate">{{ $cert->curso->titulo }}</p>
            <p class="text-sm text-Alumco-gray/55 mt-0.5">
                Emitido el {{ $cert->fecha_emision?->format('d/m/Y') ?? '—' }}
            </p>
        </div>

        {{-- Botón descargar --}}
        <a href="{{ route('mis-certificados.descargar', $cert) }}"
           class="btn-download shrink-0 bg-Alumco-blue text-white px-4 py-2 rounded-xl font-semibold text-sm">
            Descargar
        </a>
    </div>

@empty

    {{-- Estado vacío --}}
    <div class="text-center py-20">
        <div class="mx-auto w-20 h-20 rounded-full bg-Alumco-blue/10 flex items-center justify-center mb-5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-Alumco-blue/30" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 1L9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1z"/>
            </svg>
        </div>
        <p class="font-display font-bold text-Alumco-gray text-lg">Aún no tienes certificados</p>
        <p class="text-Alumco-gray/55 text-sm mt-2 max-w-xs mx-auto leading-relaxed">
            Completa un curso al 100% para obtener tu primer certificado.
        </p>
        <a href="{{ route('cursos.index') }}"
           class="btn-primary inline-block mt-6 bg-Alumco-blue text-white font-bold py-3 px-8 rounded-2xl shadow-md">
            Ir a mis cursos
        </a>
    </div>

@endforelse

@endsection
