@extends('layouts.error')

@section('title', 'Sesión expirada')
@section('code', '419')
@section('header-title', 'Tu sesión ha expirado')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        La página caducó por inactividad. Por favor, vuelve a ingresar para continuar.
    </p>

    <div class="flex flex-col gap-4 sm:flex-row">
        <a
            href="{{ route('login') }}"
            class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-Alumco-blue px-8 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all hover:shadow-xl active:scale-95 sm:w-auto"
        >
            <span>Iniciar sesión nuevamente</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 transition-transform group-hover:translate-x-1">
                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
            </svg>
        </a>

        <button
            type="button"
            onclick="history.back()"
            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200/60 bg-white px-8 py-4 text-lg font-bold text-Alumco-gray transition-all hover:border-slate-300 hover:bg-slate-50 active:scale-95 sm:w-auto"
        >
            <span>Volver</span>
        </button>
    </div>
@endsection

@section('illustration')
<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="100" cy="108" r="68" fill="#205099" opacity="0.07" stroke="#205099" stroke-width="5"/>
    <line x1="100" y1="108" x2="100" y2="54" stroke="#205099" stroke-width="6" stroke-linecap="round"/>
    <line x1="100" y1="108" x2="136" y2="128" stroke="#205099" stroke-width="6" stroke-linecap="round"/>
    <circle cx="100" cy="108" r="7" fill="#205099"/>
    <line x1="100" y1="44" x2="100" y2="56" stroke="#205099" stroke-width="5" stroke-linecap="round" opacity="0.4"/>
    <line x1="100" y1="160" x2="100" y2="172" stroke="#205099" stroke-width="5" stroke-linecap="round" opacity="0.4"/>
    <line x1="36" y1="108" x2="48" y2="108" stroke="#205099" stroke-width="5" stroke-linecap="round" opacity="0.4"/>
    <line x1="152" y1="108" x2="164" y2="108" stroke="#205099" stroke-width="5" stroke-linecap="round" opacity="0.4"/>
    <circle cx="148" cy="48" r="22" fill="#FF6364" opacity="0.12"/>
    <line x1="138" y1="38" x2="158" y2="58" stroke="#FF6364" stroke-width="5" stroke-linecap="round"/>
    <line x1="158" y1="38" x2="138" y2="58" stroke="#FF6364" stroke-width="5" stroke-linecap="round"/>
</svg>
@endsection
