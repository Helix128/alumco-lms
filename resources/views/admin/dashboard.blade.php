@extends('layouts.panel')

@section('title', 'Dashboard')

@section('header_title', 'Dashboard Analítico')

@section('content')
    <div class="space-y-8">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Usuarios --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-Alumco-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total de Usuarios Activos</p>
                        <p class="text-3xl font-bold text-Alumco-blue mt-2">{{ $stats['totalUsers'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-Alumco-blue opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                    </svg>
                </div>
            </div>

            {{-- Total Cursos --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-Alumco-cyan">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Cursos Activos</p>
                        <p class="text-3xl font-bold text-Alumco-cyan mt-2">{{ $stats['totalCursos'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-Alumco-cyan opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.669 0-3.218.51-4.5 1.385A7.968 7.968 0 009 4.804z" />
                    </svg>
                </div>
            </div>

            {{-- Certificados Año Actual --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-Alumco-green-vivid">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Certificados {{ now()->year }}</p>
                        <p class="text-3xl font-bold text-Alumco-green-vivid mt-2">{{ $stats['totalCertificados'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-Alumco-green-vivid opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 3.002v6a3 3 0 01-.709 1.895l-2.348 2.788a3 3 0 01-2.226.906H9.25a3 3 0 01-2.226-.906l-2.348-2.788A3 3 0 013 12.002v-6a3.066 3.066 0 012.267-3.002z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>

            {{-- Cumplimiento Anual --}}
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-Alumco-coral">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Cumplimiento Anual</p>
                        <p class="text-3xl font-bold text-Alumco-coral mt-2">{{ $stats['cumplimientoAnual'] }}%</p>
                    </div>
                    <svg class="w-12 h-12 text-Alumco-coral opacity-20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h.01a1 1 0 110 2H12zm-2 2a1 1 0 100 2h.01a1 1 0 100-2H10zm4 0a1 1 0 100 2h.01a1 1 0 100-2h-.01zm2-2a1 1 0 110-2h.01a1 1 0 110 2h-.01zM8 5a1 1 0 100 2h.01a1 1 0 100-2H8zm0 4a1 1 0 100 2h.01a1 1 0 100-2H8z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Gauge Anual Capacitador --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Indicador Anual de Capacitación</h3>
            <div class="flex justify-center">
                <div class="w-64">
                    @livewire('admin.gauge-anual-capacitador', ['porcentaje' => $stats['cumplimientoAnual']])
                </div>
            </div>
        </div>

        {{-- Gráfico por Sede --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Aprobación por Sede</h3>
            @livewire('admin.grafico-por-sede')
        </div>

        {{-- Gráficas de Composición --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Composición del Alumnado</h3>
            @livewire('admin.grafico-composicion')
        </div>
    </div>
@endsection
