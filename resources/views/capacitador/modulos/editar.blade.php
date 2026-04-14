@extends('layouts.panel')

@section('title', 'Editar módulo')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-sm text-Alumco-blue hover:underline mb-6 inline-block">
            ← Volver a {{ $curso->titulo }}
        </a>

        <h2 class="text-2xl font-bold text-Alumco-gray mb-6">Editar módulo</h2>

        <form action="{{ route('capacitador.cursos.modulos.update', [$curso, $modulo]) }}" method="POST"
              enctype="multipart/form-data" class="filter-card space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Título del módulo *</label>
                <input type="text" name="titulo" value="{{ old('titulo', $modulo->titulo) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30">
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Tipo de contenido</label>
                <p class="text-sm text-Alumco-gray/70 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50">
                    {{ ucfirst(\App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido) }}
                    <span class="text-xs text-Alumco-gray/40">(no se puede cambiar)</span>
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Duración estimada (minutos)</label>
                <input type="number" name="duracion_minutos" value="{{ old('duracion_minutos', $modulo->duracion_minutos) }}" min="1"
                       class="w-32 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30">
            </div>

            @if (in_array($modulo->tipo_contenido, ['video','pdf','ppt','imagen']))
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Reemplazar archivo</label>
                    @if ($modulo->ruta_archivo)
                        <p class="text-xs text-Alumco-gray/50 mb-2">
                            Archivo actual:
                            <a href="{{ asset('storage/' . $modulo->ruta_archivo) }}"
                               target="_blank" class="text-Alumco-blue hover:underline">Ver</a>
                        </p>
                    @endif
                    <input type="file" name="ruta_archivo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-Alumco-gray/50 mt-1">Déjalo vacío para conservar el archivo actual.</p>
                </div>
            @elseif ($modulo->tipo_contenido === 'texto')
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Contenido</label>
                    <textarea name="contenido" rows="8"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                     focus:ring-2 focus:ring-Alumco-blue/30 font-mono text-sm">{{ old('contenido', $modulo->contenido) }}</textarea>
                </div>
            @elseif ($modulo->tipo_contenido === 'evaluacion')
                <div class="bg-Alumco-blue/5 border border-Alumco-blue/20 rounded-xl p-4 text-sm text-Alumco-gray">
                    Para editar las preguntas de la evaluación,
                    <a href="{{ route('capacitador.cursos.modulos.evaluacion', [$curso, $modulo]) }}"
                       class="text-Alumco-blue font-semibold hover:underline">ve al editor de evaluación</a>.
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('capacitador.cursos.show', $curso) }}"
                   class="border border-gray-300 text-Alumco-gray px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-Alumco-blue text-white px-6 py-2 rounded-lg font-bold hover:brightness-110 transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
