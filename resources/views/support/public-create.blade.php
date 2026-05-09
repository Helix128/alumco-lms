@extends('layouts.auth')

@section('title', 'Soporte técnico - Alumco')

@section('content')
    <div class="overflow-hidden rounded-3xl border border-white/40 bg-white/80 shadow-2xl backdrop-blur-xl">
        <div class="bg-Alumco-blue/90 px-8 py-6 lg:px-12">
            <h1 class="font-display text-2xl font-black tracking-tight text-white sm:text-3xl">Contactar soporte</h1>
            <p class="mt-1 text-sm font-medium text-Alumco-cyan">Envía una incidencia técnica al equipo de Alumco.</p>
        </div>

        <div class="px-8 py-8 lg:px-12 lg:py-10">
            <livewire:support.create-ticket />

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-black text-Alumco-blue underline decoration-Alumco-blue/30 underline-offset-4 hover:text-Alumco-coral">Volver al login</a>
            </div>
        </div>
    </div>
@endsection
