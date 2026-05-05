@extends('layouts.panel')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Resumen de Actividad Académica</h2>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-blue/5 hover:-translate-y-1 hover:border-Alumco-blue/20">
            <div class="bg-Alumco-blue/5 rounded-2xl p-4 shrink-0 text-Alumco-blue">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-blue">{{ $stats['cursos'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Mis Cursos</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-green/5 hover:-translate-y-1 hover:border-Alumco-green/20">
            <div class="bg-Alumco-green/10 rounded-2xl p-4 shrink-0 text-Alumco-green-vivid">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-green-vivid">{{ $stats['participantes'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Participantes</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-yellow/5 hover:-translate-y-1 hover:border-Alumco-yellow/20">
            <div class="bg-Alumco-yellow/10 rounded-2xl p-4 shrink-0 text-Alumco-yellow">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-yellow">{{ $stats['certificados'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Certificados</p>
            </div>
        </div>
    </div>

    {{-- Últimos cursos --}}
    <div class="bg-white p-8 rounded-[32px] border border-gray-200 shadow-none flex flex-col overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-display font-black text-Alumco-blue">Últimos Cursos</h3>
                <a href="{{ route('capacitador.cursos.index') }}" class="text-xs font-bold text-Alumco-blue hover:underline">Ver todos</a>
            </div>
            
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="pb-3 text-[10px] font-display font-black uppercase tracking-widest text-gray-400">Título</th>
                            <th class="pb-3 text-[10px] font-display font-black uppercase tracking-widest text-gray-400 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($ultimosCursos as $curso)
                            @php
                                $tienePlanActiva = $curso['planificaciones_count'] > 0;
                                $estado = $tienePlanActiva ? 'Programado' : 'Sin Programar';
                                $badgeColor = $tienePlanActiva ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400';
                            @endphp
                            <tr class="group">
                                <td class="py-4 pr-4">
                                    <a href="{{ route('capacitador.cursos.show', $curso['id']) }}" class="font-display font-bold text-Alumco-gray group-hover:text-Alumco-blue transition-colors leading-tight block">
                                        {{ $curso['titulo'] }}
                                    </a>
                                    <span class="text-[10px] text-Alumco-gray/40 font-bold uppercase">{{ $curso['modulos_count'] }} módulos</span>
                                </td>
                                <td class="py-4 text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ $badgeColor }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-10 text-center text-Alumco-gray/30 font-medium">No has creado cursos aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
@endsection
