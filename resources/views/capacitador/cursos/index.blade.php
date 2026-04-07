@extends('layouts.panel')

@section('title', 'Mis cursos')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-Alumco-gray">Mis cursos</h2>
        <a href="{{ route('capacitador.cursos.crear') }}"
           class="bg-Alumco-blue text-white px-5 py-2 rounded-lg font-semibold hover:brightness-110 transition text-sm">
            + Nuevo curso
        </a>
    </div>

    <div class="filter-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold">Portada</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold">Título</th>
                    <th class="text-left py-2 pr-4 text-Alumco-gray/60 font-semibold hidden md:table-cell">Disponibilidad</th>
                    <th class="text-center py-2 pr-4 text-Alumco-gray/60 font-semibold hidden sm:table-cell">Módulos</th>
                    <th class="text-center py-2 pr-4 text-Alumco-gray/60 font-semibold hidden sm:table-cell">Estamentos</th>
                    <th class="text-left py-2 text-Alumco-gray/60 font-semibold">Estado</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cursos as $curso)
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
                        <td class="py-3 pr-4">
                            @if ($curso->imagen_portada)
                                <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                     class="w-12 h-10 object-cover rounded-lg">
                            @else
                                <div class="w-12 h-10 bg-Alumco-blue/10 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-Alumco-blue/40" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3L2 9l10 6 10-6-10-6z"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 pr-4 font-semibold text-Alumco-gray">
                            <a href="{{ route('capacitador.cursos.show', $curso) }}" class="hover:underline">
                                {{ $curso->titulo }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-Alumco-gray/60 hidden md:table-cell text-xs">
                            @if ($curso->fecha_inicio)
                                {{ $curso->fecha_inicio->format('d/m/Y') }} – {{ $curso->fecha_fin->format('d/m/Y') }}
                            @else
                                Sin fecha definida
                            @endif
                        </td>
                        <td class="py-3 pr-4 text-center text-Alumco-gray/70 hidden sm:table-cell">
                            {{ $curso->modulos_count }}
                        </td>
                        <td class="py-3 pr-4 text-center text-Alumco-gray/70 hidden sm:table-cell">
                            {{ $curso->estamentos_count }}
                        </td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                {{ $estado }}
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('capacitador.cursos.show', $curso) }}"
                                   class="text-Alumco-blue hover:underline text-xs font-semibold">Ver</a>
                                <a href="{{ route('capacitador.cursos.editar', $curso) }}"
                                   class="text-Alumco-gray/60 hover:text-Alumco-gray text-xs font-semibold">Editar</a>
                                <form action="{{ route('capacitador.cursos.destroy', $curso) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar este curso? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-semibold">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-Alumco-gray/40">
                            No has creado cursos aún.
                            <a href="{{ route('capacitador.cursos.crear') }}" class="text-Alumco-blue hover:underline ml-1">
                                Crear el primero
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $cursos->links() }}
        </div>
    </div>
@endsection
