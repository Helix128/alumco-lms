@extends('layouts.panel')

@section('title', 'Agregar módulo')

@section('header_title', 'Gestión de Contenido')

@section('content')
    <div class="max-w-3xl mx-auto space-y-8">
        {{-- Navegación y Título --}}
        <div>
            <a href="{{ route('capacitador.cursos.show', $curso) }}" 
               class="inline-flex items-center gap-2 text-sm font-bold text-Alumco-blue hover:text-Alumco-blue/70 transition-colors mb-4 group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al curso
            </a>
            <h2 class="text-3xl font-display font-black text-Alumco-blue">Agregar módulo</h2>
            <p class="text-Alumco-gray/50 font-bold uppercase tracking-wider text-[10px] mt-1">Nuevo recurso para: {{ $curso->titulo }}</p>
        </div>

        {{-- Formulario --}}
        <form action="{{ route('capacitador.cursos.modulos.store', $curso) }}" method="POST"
              enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10 space-y-8">
            @csrf

            <div class="grid grid-cols-1 gap-8">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Título del módulo <span class="text-Alumco-coral">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required placeholder="Ej: Introducción al sistema"
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all @error('titulo') border-Alumco-coral @enderror">
                    @error('titulo') <p class="text-Alumco-coral text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Tipo de contenido --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Tipo de contenido <span class="text-Alumco-coral">*</span></label>
                        <select name="tipo_contenido" id="tipo_contenido" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                            <option value="">— Selecciona —</option>
                            @foreach ($tipos as $valor => $etiqueta)
                                <option value="{{ $valor }}" {{ old('tipo_contenido') === $valor ? 'selected' : '' }}>
                                    {{ ucfirst($etiqueta) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Duración --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Duración estimada (minutos)</label>
                        <input type="number" name="duracion_minutos" value="{{ old('duracion_minutos') }}" min="1" placeholder="Min"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                    </div>
                </div>

                {{-- Sección dinámica: archivo --}}
                <div id="campo-archivo" class="hidden space-y-4">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Archivo del módulo</label>
                    <div class="group relative">
                        <input type="file" name="ruta_archivo"
                               class="w-full bg-Alumco-cream/30 border border-dashed border-gray-200 rounded-xl px-4 py-8 text-sm file:hidden cursor-pointer hover:bg-Alumco-blue/5 transition-all text-center font-bold text-Alumco-gray/40">
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none gap-2">
                            <svg class="w-8 h-8 text-Alumco-blue/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span class="text-xs uppercase tracking-widest font-display">Subir material (Video, PDF, PPT, Imagen)</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-Alumco-gray/40 font-bold uppercase tracking-wider text-center">Formatos soportados: MP4, PDF, PPT, PPTX, JPEG, PNG, WEBP. Máximo 100 MB.</p>
                </div>

                {{-- Sección dinámica: texto --}}
                <div id="campo-texto" class="hidden space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Contenido de texto</label>
                    <textarea name="contenido" rows="10" placeholder="Escribe o pega aquí el contenido del módulo..."
                              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all font-mono text-sm">{{ old('contenido') }}</textarea>
                    <p class="text-[10px] text-Alumco-gray/40 font-bold uppercase tracking-wider italic">Puedes usar etiquetas HTML básicas para dar formato.</p>
                </div>

                {{-- Sección dinámica: evaluación --}}
                <div id="campo-evaluacion" class="hidden">
                    <div class="p-5 bg-Alumco-blue/5 rounded-2xl border border-Alumco-blue/10 flex items-start gap-4">
                        <div class="bg-Alumco-blue/10 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-black text-Alumco-blue uppercase tracking-widest">Generación de Evaluación</span>
                            <span class="block text-xs text-Alumco-gray/60 mt-1 font-medium italic">Al guardar, se creará una evaluación vinculada. Podrás configurar las preguntas desde el panel del curso.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer de Acciones --}}
            <div class="flex items-center justify-end gap-2 pt-6 border-t border-gray-50">
                <a href="{{ route('capacitador.cursos.show', $curso) }}"
                   class="px-8 py-3.5 text-sm font-display font-black uppercase tracking-widest text-Alumco-gray/50 hover:text-Alumco-coral transition-colors text-center">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-black text-xs uppercase tracking-[0.2em] py-4 px-12 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
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
