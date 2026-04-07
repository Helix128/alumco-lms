<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-Alumco-gray text-lg">Editor de Evaluación</h3>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm text-Alumco-gray/70">Intentos semanales:</label>
                <input type="number" wire:model.lazy="max_intentos_semanales" min="1" max="10"
                       class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-Alumco-gray/70">Puntos para aprobar:</label>
                <input type="number" wire:model.lazy="puntos_aprobacion" min="0"
                       class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center">
                <span class="text-xs text-Alumco-gray/50">(70% auto)</span>
            </div>
        </div>
    </div>

    @if ($flashMensaje)
        <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ $flashMensaje }}
        </div>
    @endif

    {{-- Lista de preguntas --}}
    @forelse ($preguntas as $pi => $pregunta)
        <div class="border border-gray-200 rounded-xl p-4 mb-4 bg-gray-50">
            <div class="flex items-start gap-3">
                <span class="bg-Alumco-blue text-white rounded-full w-8 h-8 flex items-center justify-center
                             font-black text-sm shrink-0">{{ $pi + 1 }}</span>
                <div class="flex-1">
                    <input type="text"
                           wire:model.lazy="preguntas.{{ $pi }}.enunciado"
                           placeholder="Enunciado de la pregunta…"
                           class="w-full border-b border-gray-300 bg-transparent py-1 text-Alumco-gray font-semibold
                                  focus:outline-none focus:border-Alumco-blue">

                    {{-- Opciones --}}
                    <div class="mt-3 space-y-2">
                        @foreach ($pregunta['opciones'] as $oi => $opcion)
                            <div class="flex items-center gap-2">
                                {{-- Toggle correcta --}}
                                <button wire:click="toggleCorrecta({{ $opcion['id'] }})"
                                        class="w-6 h-6 rounded-full border-2 shrink-0 transition-colors
                                               {{ $opcion['es_correcta']
                                                  ? 'bg-Alumco-green-vivid border-Alumco-green-vivid'
                                                  : 'bg-white border-gray-300 hover:border-Alumco-blue' }}"
                                        title="{{ $opcion['es_correcta'] ? 'Correcta' : 'Marcar como correcta' }}">
                                </button>
                                <input type="text"
                                       wire:model.lazy="preguntas.{{ $pi }}.opciones.{{ $oi }}.texto"
                                       placeholder="Texto de la opción…"
                                       class="flex-1 border-b border-gray-200 bg-transparent py-0.5 text-sm
                                              focus:outline-none focus:border-Alumco-blue">
                                <button wire:click="eliminarOpcion({{ $opcion['id'] }})"
                                        class="text-red-400 hover:text-red-600 transition-colors shrink-0"
                                        title="Eliminar opción">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button wire:click="agregarOpcion({{ $pregunta['id'] }})"
                            class="mt-2 text-xs text-Alumco-blue hover:underline">
                        + Agregar opción
                    </button>
                </div>

                <button wire:click="eliminarPregunta({{ $pregunta['id'] }})"
                        class="text-red-400 hover:text-red-600 transition-colors shrink-0 ml-2"
                        title="Eliminar pregunta">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
    @empty
        <p class="text-Alumco-gray/50 text-sm text-center py-4">
            No hay preguntas aún. Agrega la primera.
        </p>
    @endforelse

    {{-- Agregar nueva pregunta --}}
    <div class="flex gap-2 mt-4">
        <input type="text"
               wire:model.lazy="nuevaPreguntaEnunciado"
               wire:keydown.enter="agregarPregunta"
               placeholder="Escribe una nueva pregunta y presiona Agregar…"
               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none
                      focus:ring-2 focus:ring-Alumco-blue/30">
        <button wire:click="agregarPregunta"
                class="bg-Alumco-blue text-white px-4 py-2 rounded-lg text-sm font-semibold hover:brightness-110 transition">
            Agregar
        </button>
    </div>

    {{-- Guardar --}}
    <div class="flex justify-end mt-5">
        <button wire:click="guardarTextos"
                class="bg-Alumco-green-vivid text-white px-6 py-2 rounded-lg font-bold hover:brightness-110 transition">
            Guardar cambios
        </button>
    </div>
</div>
