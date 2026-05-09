@extends('layouts.panel')

@section('title', 'Firma Institucional')

@section('header_title', 'Acreditación Institucional')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="admin-page-title">Firma Institucional</h2>
            <p class="admin-page-subtitle">Gestión de la firma del representante legal para certificados.</p>
        </div>

        @livewire('admin.institutional-signature')
    </div>
@endsection
