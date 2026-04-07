@extends('layouts.panel')

@section('title', 'Agregar módulo')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-sm text-Alumco-blue hover:underline mb-6 inline-block">
            ← Volver a {{ $curso->titulo }}
        </a>

        <h2 class="text-2xl font-bold text-Alumco-gray mb-6">Agregar módulo</h2>

        <form action="{{ route('capacitador.cursos.modulos.store', $curso) }}" method="POST"
              enctype="multipart/form-data" class="filter-card space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Título del módulo *</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30 @error('titulo') border-red-400 @enderror">
                @error('titulo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Tipo de contenido *</label>
                <select name="tipo_contenido" id="tipo_contenido" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                               focus:ring-2 focus:ring-Alumco-blue/30">
                    <option value="">— Selecciona —</option>
                    @foreach ($tipos as $valor => $etiqueta)
                        <option value="{{ $valor }}" {{ old('tipo_contenido') === $valor ? 'selected' : '' }}>
                            {{ ucfirst($etiqueta) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Duración estimada (minutos)</label>
                <input type="number" name="duracion_minutos" value="{{ old('duracion_minutos') }}" min="1"
                       class="w-32 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30">
            </div>

            {{-- Sección dinámica: archivo o texto --}}
            <div id="campo-archivo" class="hidden">
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Archivo</label>
                <input type="file" name="ruta_archivo"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-Alumco-gray/50 mt-1">
                    Máximo 100 MB. Videos MP4, PDF, PPT/PPTX, imágenes JPG/PNG.
                </p>
            </div>

            <div id="campo-texto" class="hidden">
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Contenido</label>
                <textarea name="contenido" rows="8"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                 focus:ring-2 focus:ring-Alumco-blue/30 font-mono text-sm">{{ old('contenido') }}</textarea>
                <p class="text-xs text-Alumco-gray/50 mt-1">Puedes usar HTML básico.</p>
            </div>

            <div id="campo-evaluacion" class="hidden">
                <div class="bg-Alumco-blue/5 border border-Alumco-blue/20 rounded-xl p-4 text-sm text-Alumco-gray">
                    Al guardar este módulo, se creará automáticamente una evaluación vacía.
                    Podrás agregar preguntas desde la vista del curso.
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('capacitador.cursos.show', $curso) }}"
                   class="border border-gray-300 text-Alumco-gray px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-Alumco-blue text-white px-6 py-2 rounded-lg font-bold hover:brightness-110 transition">
                    Agregar módulo
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const select = document.getElementById('tipo_contenido');
    const campoArchivo = document.getElementById('campo-archivo');
    const campoTexto = document.getElementById('campo-texto');
    const campoEval = document.getElementById('campo-evaluacion');

    function actualizarCampos() {
        const tipo = select.value;
        campoArchivo.classList.toggle('hidden', !['video','pdf','ppt','imagen'].includes(tipo));
        campoTexto.classList.toggle('hidden', tipo !== 'texto');
        campoEval.classList.toggle('hidden', tipo !== 'evaluacion');
    }

    select.addEventListener('change', actualizarCampos);
    actualizarCampos();
})();
</script>
@endpush
