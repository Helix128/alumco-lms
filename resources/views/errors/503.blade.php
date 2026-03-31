@extends('layouts.error')

@section('title', 'En mantenimiento')
@section('code', '503')
@section('header-title', 'En mantenimiento')

@section('content')
    <p class="text-lg font-medium text-Alumco-gray/80">
        @if (!empty($exception->getMessage()))
            {{ $exception->getMessage() }}
        @else
            El sitio se encuentra temporalmente en mantenimiento. Vuelve a intentarlo en unos minutos.
        @endif
    </p>

    <p class="text-sm text-Alumco-gray/50">
        Si el problema persiste, comunícate con soporte en
        <a href="mailto:soporte@alumco.org" class="font-bold text-Alumco-blue underline-offset-2 hover:underline">soporte@alumco.org</a>.
    </p>
@endsection

@section('illustration')
<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="88" cy="96" r="44" fill="#205099" opacity="0.08" stroke="#205099" stroke-width="4"/>
    <circle cx="88" cy="96" r="20" fill="white" stroke="#205099" stroke-width="4"/>
    <rect x="84" y="52" width="8" height="12" rx="4" fill="#205099" opacity="0.5"/>
    <rect x="84" y="128" width="8" height="12" rx="4" fill="#205099" opacity="0.5"/>
    <rect x="44" y="92" width="12" height="8" rx="4" fill="#205099" opacity="0.5"/>
    <rect x="120" y="92" width="12" height="8" rx="4" fill="#205099" opacity="0.5"/>
    <path d="M148 38C137 27 116 36 116 52C116 58 118 64 123 68L74 154C70 162 75 170 82 172C89 174 97 170 100 162L148 75C157 77 165 73 169 65C175 54 170 38 159 32L153 48L146 50L142 42L148 38Z" fill="#205099" opacity="0.2" stroke="#205099" stroke-width="2" stroke-linejoin="round"/>
    <circle cx="148" cy="150" r="20" fill="#205099" opacity="0.08" stroke="#205099" stroke-width="3"/>
    <circle cx="148" cy="150" r="8" fill="white" stroke="#205099" stroke-width="3"/>
    <rect x="144" y="130" width="8" height="8" rx="4" fill="#205099" opacity="0.4"/>
    <rect x="144" y="162" width="8" height="8" rx="4" fill="#205099" opacity="0.4"/>
    <rect x="128" y="146" width="8" height="8" rx="4" fill="#205099" opacity="0.4"/>
    <rect x="160" y="146" width="8" height="8" rx="4" fill="#205099" opacity="0.4"/>
</svg>
@endsection
