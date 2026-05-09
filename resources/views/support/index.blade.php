@extends('layouts.user')

@section('title', 'Soporte técnico')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="font-display text-3xl font-black text-Alumco-gray">Soporte técnico</h1>
            <p class="mt-1 text-sm font-semibold text-Alumco-gray/60">Crea tickets de soporte y revisa las solicitudes asociadas a tu cuenta.</p>
        </div>

        <livewire:support.create-ticket :embedded="true" />
        <livewire:support.my-tickets />
    </div>
@endsection
