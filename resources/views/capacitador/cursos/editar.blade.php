@extends('layouts.panel')

@section('title', 'Editar capacitación')

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
            <h2 class="text-3xl font-display font-black text-Alumco-blue">Editar capacitación</h2>
            <p class="text-Alumco-gray/50 font-bold uppercase tracking-wider text-[10px] mt-1">Actualización de propiedades y configuración</p>
        </div>

        {{-- Formulario --}}
        <form action="{{ route('capacitador.cursos.update', $curso) }}" method="POST" enctype="multipart/form-data"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-10 space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-8">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Título de la capacitación <span class="text-Alumco-coral">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo', $curso->titulo) }}" required
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all @error('titulo') border-Alumco-coral @enderror">
                    @error('titulo') <p class="text-Alumco-coral text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Descripción --}}
                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Descripción</label>
                    <textarea name="descripcion" rows="4"
                              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">{{ old('descripcion', $curso->descripcion) }}</textarea>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Nota para participantes</label>
                    <textarea name="nota_capacitador" rows="3" maxlength="1200"
                              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">{{ old('nota_capacitador', $curso->nota_capacitador) }}</textarea>
                    <p class="text-[11px] text-Alumco-gray/40 italic mt-1">Úsala solo para aclaraciones operativas relevantes de la capacitación.</p>
                </div>

                {{-- Imagen de Portada --}}
                <div class="space-y-3">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Imagen de portada</label>
                    
                    @if ($curso->imagen_portada)
                        <div class="flex items-center gap-4 p-4 bg-Alumco-cream/30 rounded-2xl border border-gray-100">
                            <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                 class="h-20 w-32 object-cover rounded-xl shadow-sm border border-white">
                            <div class="flex-1">
                                <p class="text-xs font-bold text-Alumco-gray">Portada actual activa</p>
                                <p class="text-[10px] text-Alumco-gray/40 mt-0.5">Sube una nueva imagen si deseas reemplazar la actual.</p>
                            </div>
                        </div>
                    @endif

                    <div class="group relative">
                        <input type="file" name="imagen_portada" accept="image/*"
                               class="w-full bg-Alumco-cream/30 border border-dashed border-gray-200 rounded-xl px-4 py-8 text-sm file:hidden cursor-pointer hover:bg-Alumco-blue/5 transition-all text-center font-bold text-Alumco-gray/40">
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none gap-2">
                            <svg class="w-8 h-8 text-Alumco-blue/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs uppercase tracking-widest">Nueva imagen (Max 4MB)</span>
                        </div>
                    </div>
                </div>

                {{-- Color de la capacitación --}}
                <div class="space-y-3" x-data="{ autoColor: {{ $curso->color_promedio ? 'false' : 'true' }} }">
                    <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Estética de la capacitación</label>
                    
                    <div class="flex flex-col gap-4 p-5 bg-Alumco-cream/20 rounded-2xl border border-gray-100">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="auto_color" value="1" x-model="autoColor" 
                                       class="w-5 h-5 rounded text-Alumco-blue border-gray-300 focus:ring-Alumco-blue transition-all">
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-Alumco-gray group-hover:text-Alumco-blue transition-colors">Extraer color automáticamente</p>
                                <p class="text-[10px] text-Alumco-gray/50 uppercase font-black tracking-widest mt-0.5">Sobrescribe el color al subir una imagen</p>
                            </div>
                        </label>

                        <div x-show="!autoColor" x-transition.opacity class="pt-4 border-t border-gray-100/50 flex items-center gap-6">
                            <div class="shrink-0">
                                <input type="color" name="color_promedio" value="{{ old('color_promedio', $curso->color_promedio ?? '#1a3a5a') }}"
                                       class="h-14 w-20 cursor-pointer rounded-xl border-4 border-white shadow-sm p-0 overflow-hidden">
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-Alumco-gray">Color personalizado</p>
                                <p class="text-[10px] text-Alumco-gray/40 leading-relaxed mt-1">
                                    Se mantendrá fijo a menos que vuelvas a marcar la opción automática.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>




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
