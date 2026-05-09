@extends('layouts.error')

@section('title', 'Error del servidor')
@section('code', '500')
@section('header-title', 'Error del servidor')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        Ocurrió un error inesperado en el servidor. Por favor intenta nuevamente en unos momentos.
    </p>

    <div class="flex flex-col gap-4 sm:flex-row">
        <button
            type="button"
            onclick="location.reload()"
            class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-Alumco-blue px-8 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:shadow-xl active:scale-95 sm:w-auto"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-5 w-5 transition-transform group-hover:rotate-180 duration-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span>Reintentar</span>
        </button>

        <a
            href="{{ route('login') }}"
            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-Alumco-blue/20 bg-white px-8 py-4 text-lg font-bold text-Alumco-blue transition-all hover:border-Alumco-blue/40 hover:bg-Alumco-blue/5 active:scale-95 sm:w-auto"
        >
            <span>Ir al inicio</span>
        </a>
    </div>
@endsection

@section('illustration')
<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="30" y="56" width="140" height="100" rx="12" fill="#205099" opacity="0.08" stroke="#205099" stroke-width="3"/>
    <rect x="46" y="73" width="108" height="18" rx="5" fill="#205099" opacity="0.15"/>
    <rect x="46" y="101" width="108" height="18" rx="5" fill="#205099" opacity="0.10"/>
    <rect x="46" y="129" width="108" height="14" rx="5" fill="#205099" opacity="0.07"/>
    <circle cx="60" cy="82" r="5" fill="#FF6364" opacity="0.85"/>
    <circle cx="76" cy="82" r="5" fill="#205099" opacity="0.3"/>
    <circle cx="60" cy="110" r="5" fill="#205099" opacity="0.25"/>
    <path d="M152 28L138 56H148L128 88L152 58H140L154 28Z" fill="#FF6364" opacity="0.75"/>
</svg>
@endsection
