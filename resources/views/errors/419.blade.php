@extends('layouts.error')

@section('title', 'Sesión expirada')
@section('code', '419')
@section('header-title', 'Tu sesión ha expirado')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        La página caducó por inactividad. Por favor, vuelve a ingresar para continuar.
    </p>

    <div class="flex flex-col gap-3 sm:flex-row">
        <a
            href="{{ route('login') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-Alumco-blue px-6 py-3 text-base font-bold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-Alumco-blue focus:ring-offset-2 sm:w-auto"
        >
            Iniciar sesión nuevamente
        </a>

        <button
            type="button"
            onclick="history.back()"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-slate-300 px-6 py-3 text-base font-bold text-Alumco-gray transition hover:border-Alumco-blue hover:text-Alumco-blue focus:outline-none focus:ring-2 focus:ring-Alumco-blue focus:ring-offset-2 sm:w-auto"
        >
            Volver
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
