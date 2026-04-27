<div class="space-y-6">
    @if ($flashMensaje)
        <div class="px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ $flashMensaje }}
        </div>
    @endif

    {{-- Lista de preguntas --}}
    <div class="space-y-4">
        @forelse ($preguntas as $pi => $pregunta)
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-8 relative group">
                {{-- Botón eliminar pregunta --}}
                <button wire:click="eliminarPregunta({{ $pregunta['id'] }})" 
                        class="absolute top-6 right-6 text-gray-300 hover:text-Alumco-coral transition-colors p-2"
                        title="Eliminar pregunta">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>

                <div class="space-y-6">
                    {{-- Enunciado --}}
                    <div class="flex items-start gap-4">
                        <span class="w-8 h-8 rounded-full bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center font-display font-bold text-sm shrink-0">
                            {{ $pi + 1 }}
                        </span>
                        <div class="flex-1">
                            <input type="text"
                                   wire:model.lazy="preguntas.{{ $pi }}.enunciado"
                                   placeholder="Escribe el enunciado de la pregunta..."
                                   class="w-full text-lg font-bold text-Alumco-gray border-b border-gray-100 focus:border-Alumco-blue focus:outline-none transition-all py-1"
                            >
                        </div>
                    </div>

                    {{-- Opciones --}}
                    <div class="ml-12 space-y-3">
                        @foreach ($pregunta['opciones'] as $oi => $opcion)
                            <div class="flex items-center gap-3 group/option">
                                {{-- Indicador de correcta --}}
                                <button wire:click="toggleCorrecta({{ $opcion['id'] }})"
                                        class="w-5 h-5 rounded-full border-2 shrink-0 transition-all flex items-center justify-center
                                               {{ $opcion['es_correcta'] 
                                                  ? 'bg-Alumco-green-vivid border-Alumco-green-vivid text-white' 
                                                  : 'bg-white border-gray-200 hover:border-Alumco-blue' }}"
                                        title="Marcar como correcta">
                                    @if($opcion['es_correcta'])
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>

                                <input type="text"
                                       wire:model.lazy="preguntas.{{ $pi }}.opciones.{{ $oi }}.texto"
                                       placeholder="Opción {{ $oi + 1 }}..."
                                       class="flex-1 text-sm text-Alumco-gray border-b border-transparent focus:border-gray-100 focus:outline-none transition-all py-0.5 {{ $opcion['es_correcta'] ? 'font-bold text-Alumco-green-vivid' : '' }}"
                                >

                                {{-- Botón eliminar opción --}}
                                <button wire:click="eliminarOpcion({{ $opcion['id'] }})" 
                                        class="text-gray-300 hover:text-Alumco-coral transition-colors p-1"
                                        title="Eliminar alternativa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach

                        <button wire:click="agregarOpcion({{ $pregunta['id'] }})" 
                                class="flex items-center gap-2 text-xs font-medium text-Alumco-blue hover:text-Alumco-blue/70 transition-colors pt-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Añadir alternativa
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-12 bg-white rounded-[24px] shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3">
                <p class="text-sm font-medium text-Alumco-gray/40">No hay preguntas creadas</p>
            </div>
        @endforelse
    </div>

    {{-- Nueva Pregunta --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 flex flex-col items-center gap-4">
        <input type="text"
               wire:model.lazy="nuevaPreguntaEnunciado"
               wire:keydown.enter="agregarPregunta"
               placeholder="Escribe una nueva pregunta aquí..."
               class="w-full max-w-xl bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-Alumco-blue/20 outline-none text-center"
        >
        <button wire:click="agregarPregunta" 
                class="bg-white border border-gray-200 hover:border-Alumco-blue hover:text-Alumco-blue text-Alumco-gray font-medium text-sm px-6 py-2.5 rounded-xl transition-all">
            + Añadir pregunta a la evaluación
        </button>
    </div>

    {{-- Footer de Guardado --}}
    <div class="flex items-center justify-end gap-3 pt-4">
        <a href="{{ route('capacitador.cursos.show', $curso) }}"
           class="px-6 py-2.5 text-sm font-medium text-Alumco-gray/60 hover:text-Alumco-coral transition-colors">
            Cancelar
        </a>
        <button wire:click="guardarTextos" 
                class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-2.5 px-8 rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Guardar evaluación
        </button>
    </div>
</div>
