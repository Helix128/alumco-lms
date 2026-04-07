@extends('layouts.panel')

@section('title', 'Editar curso')

@section('content')
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('capacitador.cursos.show', $curso) }}" class="text-Alumco-blue hover:underline text-sm">
                ← Volver al curso
            </a>
        </div>

        <h2 class="text-2xl font-bold text-Alumco-gray mb-6">Editar curso</h2>

        <form action="{{ route('capacitador.cursos.update', $curso) }}" method="POST" enctype="multipart/form-data"
              class="filter-card space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Título *</label>
                <input type="text" name="titulo" value="{{ old('titulo', $curso->titulo) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30 @error('titulo') border-red-400 @enderror">
                @error('titulo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                 focus:ring-2 focus:ring-Alumco-blue/30">{{ old('descripcion', $curso->descripcion) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Imagen de portada</label>
                @if ($curso->imagen_portada)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                             class="h-16 w-24 object-cover rounded-lg border border-gray-200">
                        <span class="text-xs text-Alumco-gray/50">Portada actual. Sube una nueva para reemplazarla.</span>
                    </div>
                @endif
                <input type="file" name="imagen_portada" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-Alumco-gray/50 mt-1">Máximo 4 MB.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Fecha de inicio *</label>
                    <input type="date" name="fecha_inicio"
                           value="{{ old('fecha_inicio', $curso->fecha_inicio?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                  focus:ring-2 focus:ring-Alumco-blue/30">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Fecha de cierre *</label>
                    <input type="date" name="fecha_fin"
                           value="{{ old('fecha_fin', $curso->fecha_fin?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                  focus:ring-2 focus:ring-Alumco-blue/30">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="es_secuencial" id="es_secuencial" value="1"
                       {{ old('es_secuencial', $curso->es_secuencial) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300">
                <label for="es_secuencial" class="text-sm font-semibold text-Alumco-gray">
                    Módulos secuenciales
                </label>
            </div>

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
