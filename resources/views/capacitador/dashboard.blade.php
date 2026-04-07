@extends('layouts.panel')

@section('title', 'Mi panel')

@section('content')
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="filter-card flex items-center gap-4">
            <div class="bg-Alumco-blue/10 rounded-xl p-3">
                <svg class="w-8 h-8 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-black text-Alumco-blue">{{ $stats['cursos'] }}</p>
                <p class="text-sm text-Alumco-gray/60">Mis cursos</p>
            </div>
        </div>

        <div class="filter-card flex items-center gap-4">
            <div class="bg-Alumco-green/40 rounded-xl p-3">
                <svg class="w-8 h-8 text-Alumco-green-vivid" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-black text-Alumco-green-vivid">{{ $stats['participantes'] }}</p>
                <p class="text-sm text-Alumco-gray/60">Participantes únicos</p>
            </div>
        </div>

        <div class="filter-card flex items-center gap-4">
            <div class="bg-Alumco-yellow/30 rounded-xl p-3">
                <svg class="w-8 h-8 text-Alumco-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-black text-Alumco-yellow">{{ $stats['certificados'] }}</p>
                <p class="text-sm text-Alumco-gray/60">Certificados emitidos</p>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="filter-card mb-8">
        <h2 class="text-lg font-bold text-Alumco-gray mb-4">Porcentaje de completado por curso</h2>
        @livewire('capacitador.estadisticas-dashboard', ['capacitadorId' => auth()->id()])
    </div>

    {{-- Últimos cursos --}}
    <div class="filter-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-Alumco-gray">Mis últimos cursos</h2>
            <a href="{{ route('capacitador.cursos.index') }}" class="text-sm text-Alumco-blue hover:underline">Ver todos</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold">Título</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold hidden sm:table-cell">Módulos</th>
                    <th class="text-left py-2 text-Alumco-gray/60 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ultimosCursos as $curso)
                    @php
                        $hoy = now()->toDateString();
                        $estado = !$curso->fecha_inicio ? 'Sin fecha'
                            : ($hoy < $curso->fecha_inicio->toDateString() ? 'Próximo'
                            : ($hoy > $curso->fecha_fin->toDateString() ? 'Finalizado' : 'Activo'));
                        $badgeColor = match($estado) {
                            'Activo'    => 'bg-Alumco-green-vivid/20 text-Alumco-green-vivid',
                            'Próximo'   => 'bg-Alumco-blue/10 text-Alumco-blue',
                            'Finalizado'=> 'bg-gray-100 text-gray-500',
                            default     => 'bg-gray-100 text-gray-400',
                        };
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 pr-4 font-semibold text-Alumco-gray">
                            <a href="{{ route('capacitador.cursos.show', $curso) }}" class="hover:underline">
                                {{ $curso->titulo }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-Alumco-gray/60 hidden sm:table-cell">{{ $curso->modulos_count }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                {{ $estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-Alumco-gray/40">No has creado cursos aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
