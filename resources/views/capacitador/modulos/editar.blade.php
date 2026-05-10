@extends('layouts.panel')

@section('title', 'Editar módulo')

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
                Volver a la capacitación
            </a>
            <h2 class="text-3xl font-display font-black text-Alumco-blue">Editar módulo</h2>
            <p class="text-Alumco-gray/50 font-bold uppercase tracking-wider text-[10px] mt-1">Modificando: {{ $modulo->titulo }}</p>
        </div>

        {{-- Formulario --}}
        <form action="{{ route('capacitador.cursos.modulos.update', [$curso, $modulo]) }}" method="POST"
              enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10 space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-8">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Título del módulo <span class="text-Alumco-coral">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo', $modulo->titulo) }}" required
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Tipo de contenido --}}
                    <div class="space-y-2 opacity-60">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Tipo de contenido</label>
                        <div class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3.5 text-Alumco-gray font-bold text-sm select-none">
                            {{ ucfirst(\App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido) }}
                            <span class="text-[10px] text-Alumco-gray/40 ml-2">(No editable)</span>
                        </div>
                    </div>

                    {{-- Duración --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Duración estimada (minutos)</label>
                        <input type="number" name="duracion_minutos" value="{{ old('duracion_minutos', $modulo->duracion_minutos) }}" min="1"
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                    </div>
                </div>

                @if (in_array($modulo->tipo_contenido, ['video','pdf','ppt','imagen']))
                    {{-- Sección archivo --}}
                    <div class="space-y-4">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Reemplazar archivo material</label>
                        
                        @if ($modulo->ruta_archivo)
                            <div class="flex items-center gap-4 p-4 bg-Alumco-blue/5 rounded-2xl border border-Alumco-blue/10">
                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-Alumco-blue shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-Alumco-gray truncate">Material actual cargado</p>
                                    <a href="{{ asset('storage/' . $modulo->ruta_archivo) }}" target="_blank" 
                                       class="text-[10px] font-black uppercase tracking-widest text-Alumco-blue hover:underline">Vista previa del archivo</a>
                                </div>
                            </div>
                        @endif

                        <div class="group relative">
                            <input type="file" name="ruta_archivo"
                                   class="w-full bg-Alumco-cream/30 border border-dashed border-gray-200 rounded-xl px-4 py-8 text-sm file:hidden cursor-pointer hover:bg-Alumco-blue/5 transition-all text-center font-bold text-Alumco-gray/40">
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none gap-2">
                                <svg class="w-8 h-8 text-Alumco-blue/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <span class="text-xs uppercase tracking-widest font-display">Click para subir nuevo material (Video, PDF, PPT, Imagen)</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-Alumco-gray/40 font-bold uppercase tracking-wider text-center italic">Formatos: MP4, PDF, PPT, PPTX, JPEG, PNG, WEBP. Máximo 100 MB.</p>
                    </div>

                @elseif ($modulo->tipo_contenido === 'texto')
                    {{-- Sección texto --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Contenido de texto</label>
                        <textarea name="contenido" rows="12"
                                  class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all font-mono text-sm">{{ old('contenido', $modulo->contenido) }}</textarea>
                    </div>

                @elseif ($modulo->tipo_contenido === 'evaluacion')
                    {{-- Sección evaluación --}}
                    <div class="p-8 bg-Alumco-green/5 rounded-3xl border border-Alumco-green/10 flex flex-col items-center text-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-Alumco-green-vivid shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-Alumco-gray uppercase tracking-widest">Configuración de Evaluación</h4>
                            <p class="text-xs text-Alumco-gray/50 mt-2 max-w-sm mx-auto leading-relaxed">Las preguntas y respuestas de este módulo se gestionan de forma independiente.</p>
                        </div>
                        <a href="{{ route('capacitador.cursos.modulos.evaluacion', [$curso, $modulo]) }}"
                           class="inline-flex items-center gap-2 bg-white border border-gray-100 text-Alumco-blue font-display font-black text-[10px] uppercase tracking-widest py-3 px-8 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 mt-2">
                            Gestionar preguntas y opciones
                        </a>
                    </div>
                @endif
            </div>

            {{-- Footer de Acciones --}}
            <div class="flex items-center justify-end gap-2 pt-6 border-t border-gray-50">
                <a href="{{ route('capacitador.cursos.show', $curso) }}"
                   class="px-8 py-3.5 text-sm font-display font-black uppercase tracking-widest text-Alumco-gray/50 hover:text-Alumco-coral transition-colors text-center">
                    Cancelar cambios
                </a>
                <button type="submit"
                        class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-black text-xs uppercase tracking-[0.2em] py-4 px-12 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
