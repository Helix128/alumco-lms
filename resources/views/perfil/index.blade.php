@extends('layouts.user')
@section('title', 'Mi perfil — Alumco')

@section('content')

@php
    $initials = collect(explode(' ', trim($user->name)))
        ->map(fn($w) => strtoupper($w[0] ?? ''))
        ->take(2)
        ->join('');
@endphp

{{-- HERO CARD --}}
<div class="bg-gradient-to-br from-Alumco-blue to-[#163c7a] rounded-3xl p-6 text-white shadow-md">

    {{-- Avatar de iniciales --}}
    <div class="w-20 h-20 rounded-full bg-white/20 border-2 border-white/40
                flex items-center justify-center mx-auto mb-4">
        <span class="font-display font-black text-3xl leading-none">{{ $initials }}</span>
    </div>

    {{-- Nombre y email --}}
    <p class="font-display font-black text-2xl text-center leading-tight">{{ $user->name }}</p>
    <p class="text-white/70 text-sm text-center mt-0.5">{{ $user->email }}</p>

    {{-- Sede y estamento --}}
    <div class="flex items-center justify-center gap-3 mt-3 flex-wrap">
        @if ($user->sede)
            <span class="flex items-center gap-1.5 text-white/80 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                {{ $user->sede->nombre }}
            </span>
        @endif
        @if ($user->estamento)
            <span class="bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                {{ $user->estamento->nombre }}
            </span>
        @endif
    </div>
</div>

{{-- ESTADÍSTICAS --}}
<div class="grid grid-cols-3 gap-3 mt-5">
    <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
        <p class="font-display font-black text-3xl text-Alumco-blue">{{ $totalCursos }}</p>
        <p class="text-[11px] text-Alumco-gray/60 mt-0.5 leading-tight font-medium">Asignados</p>
    </div>
    <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
        <p class="font-display font-black text-3xl text-Alumco-green-vivid">{{ $cursosCompletados }}</p>
        <p class="text-[11px] text-Alumco-gray/60 mt-0.5 leading-tight font-medium">Completados</p>
    </div>
    <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
        <p class="font-display font-black text-3xl text-Alumco-yellow">{{ $cursosEnProgreso }}</p>
        <p class="text-[11px] text-Alumco-gray/60 mt-0.5 leading-tight font-medium">En progreso</p>
    </div>
</div>

{{-- INFORMACIÓN PERSONAL --}}
<div class="mt-6">
    <h3 class="font-display font-bold text-Alumco-gray text-xs uppercase tracking-widest mb-3 px-1">
        Información
    </h3>
    <div class="bg-white rounded-2xl divide-y divide-gray-100 shadow-sm border border-gray-100">

        {{-- Email --}}
        <div class="flex items-center gap-3 px-5 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-Alumco-blue/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
            </svg>
            <div>
                <p class="text-xs text-Alumco-gray/50 font-medium">Correo electrónico</p>
                <p class="text-sm font-semibold text-Alumco-gray">{{ $user->email }}</p>
            </div>
        </div>

        {{-- Fecha de nacimiento --}}
        <div class="flex items-center gap-3 px-5 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-Alumco-blue/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
            </svg>
            <div>
                <p class="text-xs text-Alumco-gray/50 font-medium">Fecha de nacimiento</p>
                <p class="text-sm font-semibold text-Alumco-gray">
                    {{ $user->fecha_nacimiento
                        ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y')
                        : '—' }}
                </p>
            </div>
        </div>

        {{-- Sede --}}
        <div class="flex items-center gap-3 px-5 py-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-Alumco-blue/60 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
            <div>
                <p class="text-xs text-Alumco-gray/50 font-medium">Sede</p>
                <p class="text-sm font-semibold text-Alumco-gray">
                    {{ $user->sede?->nombre ?? 'Sin sede asignada' }}
                </p>
            </div>
        </div>

    </div>
</div>

{{-- CERTIFICADOS RECIENTES --}}
@if ($certificados->isNotEmpty())
<div class="mt-6">
    <h3 class="font-display font-bold text-Alumco-gray text-xs uppercase tracking-widest mb-3 px-1">
        Certificados recientes
    </h3>
    <div class="flex flex-col gap-2">
        @foreach ($certificados as $cert)
            <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow-sm border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-Alumco-green/40 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-Alumco-green-vivid" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1L9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-Alumco-gray text-sm truncate">{{ $cert->curso->titulo }}</p>
                    <p class="text-xs text-Alumco-gray/55 mt-0.5">
                        {{ $cert->fecha_emision?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
                <a href="{{ route('mis-certificados.descargar', $cert) }}"
                   class="shrink-0 text-Alumco-blue text-xs font-bold hover:underline">
                    Descargar
                </a>
            </div>
        @endforeach

        @if ($certificados->count() >= 5)
            <a href="{{ route('mis-certificados.index') }}"
               class="text-center text-Alumco-blue text-sm font-semibold py-2 hover:underline">
                Ver todos mis certificados →
            </a>
        @endif
    </div>
</div>
@endif

{{-- CERRAR SESIÓN --}}
<div class="mt-8 mb-4">
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
                class="btn-logout w-full py-4 rounded-2xl border-2 border-Alumco-coral text-Alumco-coral
                       font-bold text-base">
            Cerrar sesión
        </button>
    </form>
</div>

@endsection
