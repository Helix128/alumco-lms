@extends('layouts.panel')

@section('title', 'Participantes — ' . $curso->titulo)

@section('content')
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-sm text-Alumco-blue hover:underline">
            ← Volver al curso
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-Alumco-gray">Participantes — {{ $curso->titulo }}</h2>
        @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
            <a href="{{ route('capacitador.cursos.participantes.exportar', $curso) }}"
               class="border border-Alumco-green-vivid text-Alumco-green-vivid px-4 py-2 rounded-lg
                      text-sm font-semibold hover:bg-Alumco-green/20 transition">
                Exportar Excel
            </a>
        @endif
    </div>

    {{-- Gestión de estamentos (Interno o Admin) --}}
    @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
        <div class="filter-card mb-6">
            <h3 class="font-bold text-Alumco-gray mb-4">Asignación de estamentos</h3>
            @livewire('capacitador.gestion-estamentos', ['curso' => $curso])
        </div>
    @endif

    {{-- Tabla de participantes --}}
    <div class="filter-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold">Nombre</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold hidden sm:table-cell">Estamento</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold hidden md:table-cell">Sede</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold">Progreso</th>
                    <th class="text-left py-2 text-Alumco-gray/60 font-semibold">Certificado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 pr-4">
                            <p class="font-semibold text-Alumco-gray">{{ $usuario->name }}</p>
                            <p class="text-xs text-Alumco-gray/50">{{ $usuario->email }}</p>
                        </td>
                        <td class="py-3 pr-4 text-Alumco-gray/70 hidden sm:table-cell">
                            {{ $usuario->estamento?->nombre ?? '—' }}
                        </td>
                        <td class="py-3 pr-4 text-Alumco-gray/70 hidden md:table-cell">
                            {{ $usuario->sede?->nombre ?? '—' }}
                        </td>
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full
                                                {{ $usuario->progreso_porcentaje >= 100
                                                    ? 'bg-Alumco-green-vivid'
                                                    : 'bg-Alumco-blue' }}"
                                         style="width: {{ $usuario->progreso_porcentaje }}%">
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-Alumco-gray/70">
                                    {{ $usuario->progreso_porcentaje }}%
                                </span>
                            </div>
                        </td>
                        <td class="py-3">
                            @if ($usuario->certificado)
                                <a href="{{ route('capacitador.certificados.descargar', $usuario->certificado) }}"
                                   class="inline-block bg-Alumco-green-vivid/20 text-Alumco-green-vivid
                                          px-3 py-1 rounded-full text-xs font-semibold hover:brightness-95 transition">
                                    Descargar
                                </a>
                            @elseif ($usuario->progreso_porcentaje >= 100)
                                <form action="{{ route('capacitador.certificados.generar', [$curso, $usuario]) }}"
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="bg-Alumco-blue/10 text-Alumco-blue px-3 py-1 rounded-full
                                                   text-xs font-semibold hover:bg-Alumco-blue/20 transition">
                                        Generar
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-Alumco-gray/40">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-Alumco-gray/40">
                            No hay participantes en este curso aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
