@extends('layouts.panel')

@section('title', 'Configuración del Sistema')

@section('header_title', 'Variables de Negocio')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="admin-page-title">Configuración Global</h2>
            <p class="admin-page-subtitle">(Solo visible para desarrolladores)</p>
        </div>

        <div class="admin-surface p-6">
            @livewire('dev-config')
        </div>
    </div>
@endsection
