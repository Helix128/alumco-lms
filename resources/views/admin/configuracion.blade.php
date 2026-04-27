@extends('layouts.panel')

@section('title', 'Configuración del Sistema')

@section('header_title', 'Variables de Negocio')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="text-3xl font-display font-black text-Alumco-blue">Configuración Global</h2>
            <p class="text-Alumco-gray/50 font-bold uppercase tracking-wider text-xs mt-1">(Solo visible para desarrolladores)</p>
        </div>

        @livewire('dev-config')
    </div>
@endsection
