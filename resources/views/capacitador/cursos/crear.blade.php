@extends('layouts.panel')

@section('title', 'Nuevo curso')

@section('content')
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('capacitador.cursos.index') }}" class="text-Alumco-blue hover:underline text-sm">
                ← Volver a mis cursos
            </a>
        </div>

        <h2 class="text-2xl font-bold text-Alumco-gray mb-6">Crear nuevo curso</h2>

        <form action="{{ route('capacitador.cursos.store') }}" method="POST" enctype="multipart/form-data"
              class="filter-card space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Título *</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                              focus:ring-2 focus:ring-Alumco-blue/30 @error('titulo') border-red-400 @enderror">
                @error('titulo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                 focus:ring-2 focus:ring-Alumco-blue/30">{{ old('descripcion') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-Alumco-gray mb-1">Imagen de portada</label>
                <input type="file" name="imagen_portada" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              @error('imagen_portada') border-red-400 @enderror">
                <p class="text-xs text-Alumco-gray/50 mt-1">Máximo 4 MB. Formatos: JPG, PNG, WebP.</p>
                @error('imagen_portada')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Fecha de inicio *</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                  focus:ring-2 focus:ring-Alumco-blue/30 @error('fecha_inicio') border-red-400 @enderror">
                    @error('fecha_inicio')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-Alumco-gray mb-1">Fecha de cierre *</label>
                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none
                                  focus:ring-2 focus:ring-Alumco-blue/30 @error('fecha_fin') border-red-400 @enderror">
                    @error('fecha_fin')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="es_secuencial" id="es_secuencial" value="1"
                       {{ old('es_secuencial', '1') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300">
                <label for="es_secuencial" class="text-sm font-semibold text-Alumco-gray">
                    Módulos secuenciales
                    <span class="text-Alumco-gray/50 font-normal">(el alumno debe completar cada módulo antes de ver el siguiente)</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('capacitador.cursos.index') }}"
                   class="border border-gray-300 text-Alumco-gray px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-Alumco-blue text-white px-6 py-2 rounded-lg font-bold hover:brightness-110 transition">
                    Crear curso
                </button>
            </div>
        </form>
    </div>
@endsection
