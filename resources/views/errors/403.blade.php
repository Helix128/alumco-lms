@extends('layouts.error')

@section('title', 'Sin permiso')
@section('code', '403')
@section('header-title', 'Sin permiso')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        {{ $exception->getMessage() ?: 'No tienes permiso para acceder a este recurso.' }}
    </p>

    <div class="flex flex-col gap-3 sm:flex-row">
        @if (auth()->check())
            {{-- Usuario autenticado: ofrecer cerrar sesión --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-Alumco-coral px-6 py-3 text-base font-bold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-Alumco-coral focus:ring-offset-2 sm:w-auto"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        @endif

        <a
            href="{{ route('login') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-Alumco-blue px-6 py-3 text-base font-bold text-Alumco-blue transition hover:bg-Alumco-blue hover:text-Alumco-yellow focus:outline-none focus:ring-2 focus:ring-Alumco-blue focus:ring-offset-2 sm:w-auto"
        >
            Ir al inicio
        </a>
    </div>
@endsection

@section('illustration')
<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="100" cy="100" r="80" fill="#205099" opacity="0.07"/>
    <rect x="54" y="104" width="92" height="72" rx="12" fill="#205099" opacity="0.15" stroke="#205099" stroke-width="3"/>
    <path d="M68 104V76C68 51 132 51 132 76V104" stroke="#205099" stroke-width="9" stroke-linecap="round" fill="none"/>
    <circle cx="100" cy="136" r="11" fill="#205099"/>
    <rect x="96" y="138" width="8" height="20" rx="4" fill="#205099"/>
    <circle cx="152" cy="52" r="8" fill="#FF6364" opacity="0.65"/>
    <circle cx="44" cy="68" r="5" fill="#205099" opacity="0.25"/>
    <circle cx="160" cy="110" r="4" fill="#205099" opacity="0.2"/>
</svg>
@endsection
