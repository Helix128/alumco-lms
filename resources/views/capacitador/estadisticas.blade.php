@extends('layouts.panel')

@section('title', 'Estadísticas')

@section('header_title', 'Estadísticas')

@section('content')
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            @livewire('capacitador.estadisticas-dashboard', ['capacitadorId' => $capacitadorId])
        </div>
    </div>
@endsection
