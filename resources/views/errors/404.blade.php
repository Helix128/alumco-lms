@extends('layouts.error')

@section('title', 'Página no encontrada')
@section('code', '404')
@section('header-title', 'Página no encontrada')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        La página que buscas no existe o fue movida.
    </p>

    <div class="flex flex-col gap-4 sm:flex-row">
        @if (auth()->check())
            <a
                href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}"
                class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-Alumco-blue px-8 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:shadow-xl active:scale-95 sm:w-auto"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-5 w-5 transition-transform group-hover:-translate-x-1" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Volver</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-Alumco-blue px-8 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:shadow-xl active:scale-95 sm:w-auto"
            >
                <span>Ir al inicio de sesión</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 transition-transform group-hover:translate-x-1">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif
    </div>
@endsection

@section('illustration')
<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="86" cy="86" r="58" fill="#205099" opacity="0.07" stroke="#205099" stroke-width="5"/>
    <line x1="130" y1="130" x2="170" y2="170" stroke="#205099" stroke-width="13" stroke-linecap="round"/>
    <path d="M72 68C72 55 118 55 118 78C118 91 100 93 100 108" stroke="#205099" stroke-width="7" stroke-linecap="round" fill="none"/>
    <circle cx="100" cy="118" r="5" fill="#205099"/>
    <circle cx="155" cy="44" r="7" fill="#FF6364" opacity="0.65"/>
    <circle cx="32" cy="62" r="4" fill="#205099" opacity="0.25"/>
    <circle cx="40" cy="120" r="3" fill="#FF6364" opacity="0.3"/>
</svg>
@endsection
