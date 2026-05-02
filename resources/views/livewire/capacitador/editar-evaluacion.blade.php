<div class="space-y-6">
    {{-- Flash --}}
    @if ($flashMensaje)
        <div class="px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $flashMensaje }}
        </div>
    @endif

    {{-- Summary card --}}
    @if (count($preguntas) > 0)
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center font-display font-black text-lg">
                    {{ $this->resumen['total'] }}
                </span>
                <p class="text-xs font-black text-Alumco-gray uppercase tracking-widest">Preguntas</p>
            </div>

            <div class="w-px h-8 bg-gray-100 hidden sm:block"></div>

            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-Alumco-green-vivid/10 text-Alumco-green-vivid flex items-center justify-center font-display font-black text-lg">
                    {{ $this->resumen['puntosNecesarios'] }}
                </span>
                <div>
                    <p class="text-xs font-black text-Alumco-gray uppercase tracking-widest">Para aprobar</p>
                    <p class="text-[10px] text-Alumco-gray/50 font-bold">{{ $this->resumen['porcentaje'] }}% de {{ $this->resumen['total'] }}</p>
                </div>
            </div>

            @if ($this->resumen['preguntasSinOpciones'] > 0 || $this->resumen['preguntasSinCorrecta'] > 0)
                <div class="sm:ml-auto flex items-center gap-2 bg-amber-50 border border-amber-100 text-amber-700 rounded-xl px-4 py-2 text-xs font-bold">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    @if ($this->resumen['preguntasSinOpciones'] > 0)
                        {{ $this->resumen['preguntasSinOpciones'] }} {{ $this->resumen['preguntasSinOpciones'] === 1 ? 'pregunta' : 'preguntas' }} sin alternativas.
                    @endif
                    @if ($this->resumen['preguntasSinCorrecta'] > 0)
                        {{ $this->resumen['preguntasSinCorrecta'] }} {{ $this->resumen['preguntasSinCorrecta'] === 1 ? 'pregunta' : 'preguntas' }} sin respuesta correcta.
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Lista de preguntas --}}
    <div id="preguntas-lista" class="space-y-4">
        @forelse ($preguntas as $pi => $pregunta)
            @php
                $sinOpciones = count($pregunta['opciones']) === 0;
                $sinCorrecta = !$sinOpciones && !collect($pregunta['opciones'])->contains('es_correcta', true);
            @endphp
            <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 pl-12 pr-8 py-8 relative group pregunta-item"
                 wire:key="pregunta-{{ $pregunta['id'] }}"
                 draggable="true"
                 data-pregunta-id="{{ $pregunta['id'] }}">

                {{-- Drag handle --}}
                <div class="absolute top-9 left-4 cursor-grab active:cursor-grabbing text-gray-200 hover:text-Alumco-blue/40 transition-colors pregunta-handle select-none"
                     title="Arrastrar para reordenar">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6h2v2H8V6zm0 4h2v2H8v-2zm0 4h2v2H8v-2zm6-8h2v2h-2V6zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                    </svg>
                </div>

                {{-- Botón eliminar pregunta --}}
                <button wire:click="iniciarEliminarPregunta({{ $pregunta['id'] }})"
                        class="absolute top-6 right-6 text-gray-300 hover:text-Alumco-coral transition-colors p-2"
                        title="Eliminar pregunta">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>

                <div class="space-y-6">
                    {{-- Enunciado --}}
                    <div class="flex items-start gap-4">
                        <span class="w-8 h-8 rounded-full bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center font-display font-bold text-sm shrink-0 mt-0.5">
                            {{ $pi + 1 }}
                        </span>
                        <div class="flex-1 flex items-start gap-3 min-w-0">
                            <input type="text"
                                   wire:model.blur="preguntas.{{ $pi }}.enunciado"
                                   wire:blur="guardarEnunciado({{ $pregunta['id'] }})"
                                   placeholder="Escribe el enunciado de la pregunta..."
                                   class="flex-1 min-w-0 text-lg font-bold text-Alumco-gray border-b border-gray-100 focus:border-Alumco-blue focus:outline-none transition-all py-1"
                            >
                            @if ($sinOpciones)
                                <span class="shrink-0 text-[10px] font-black uppercase tracking-widest bg-red-50 text-Alumco-coral border border-red-100 px-2.5 py-1 rounded-full mt-1">
                                    Sin alternativas
                                </span>
                            @elseif ($sinCorrecta)
                                <span class="shrink-0 text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-100 px-2.5 py-1 rounded-full mt-1">
                                    Sin respuesta correcta
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Opciones --}}
                    <div class="ml-12 space-y-3">
                        @foreach ($pregunta['opciones'] as $oi => $opcion)
                            <div class="flex items-center gap-3 group/option">
                                {{-- Toggle correcta --}}
                                <button wire:click="toggleCorrecta({{ $opcion['id'] }})"
                                        class="w-5 h-5 rounded-full border-2 shrink-0 transition-all flex items-center justify-center
                                               {{ $opcion['es_correcta']
                                                  ? 'bg-Alumco-green-vivid border-Alumco-green-vivid text-white'
                                                  : 'bg-white border-gray-200 hover:border-Alumco-blue' }}"
                                        title="Marcar como correcta">
                                    @if($opcion['es_correcta'])
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>

                                <input type="text"
                                       wire:model.blur="preguntas.{{ $pi }}.opciones.{{ $oi }}.texto"
                                       wire:blur="guardarTextoOpcion({{ $opcion['id'] }})"
                                       placeholder="Opción {{ $oi + 1 }}..."
                                       @if($loop->last) data-last-opcion="{{ $pregunta['id'] }}" @endif
                                       class="flex-1 text-sm text-Alumco-gray border-b border-transparent focus:border-gray-100 focus:outline-none transition-all py-0.5 {{ $opcion['es_correcta'] ? 'font-bold text-Alumco-green-vivid' : '' }}"
                                >

                                {{-- Eliminar opción --}}
                                <button wire:click="iniciarEliminarOpcion({{ $opcion['id'] }})"
                                        class="text-gray-300 hover:text-Alumco-coral transition-colors p-1 opacity-0 group-hover/option:opacity-100"
                                        title="Eliminar alternativa">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach

                        <button wire:click="agregarOpcion({{ $pregunta['id'] }})"
                                class="flex items-center gap-2 text-xs font-medium text-Alumco-blue hover:text-Alumco-blue/70 transition-colors pt-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Añadir alternativa
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 bg-white rounded-[24px] shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-5 text-center">
                <div class="w-16 h-16 rounded-2xl bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-display font-black text-Alumco-gray text-lg">Esta evaluación no tiene preguntas aún</p>
                    <p class="text-sm text-Alumco-gray/60 mt-1 max-w-xs">Escribe la primera pregunta abajo y pulsa Enter para comenzar.</p>
                </div>
                <svg class="w-6 h-6 text-Alumco-gray/30 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        @endforelse
    </div>

    {{-- Nueva Pregunta --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 flex flex-col items-center gap-4">
        <input type="text"
               wire:model="nuevaPreguntaEnunciado"
               wire:keydown.enter="agregarPregunta"
               placeholder="Escribe una nueva pregunta aquí..."
               class="w-full max-w-xl bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-Alumco-blue/20 outline-none text-center"
        >
        <button wire:click="agregarPregunta"
                class="bg-white border border-gray-200 hover:border-Alumco-blue hover:text-Alumco-blue text-Alumco-gray font-medium text-sm px-6 py-2.5 rounded-xl transition-all">
            + Añadir pregunta a la evaluación
        </button>
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-end pt-4">
        <a href="{{ route('capacitador.cursos.show', $curso) }}"
           class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-2.5 px-8 rounded-xl shadow-sm transition-all active:scale-95 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al curso
        </a>
    </div>

    {{-- Modal de confirmación de eliminación --}}
    @if ($deletingType === 'pregunta')
        <div class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:key="modal-pregunta">
            <div class="bg-white rounded-[24px] shadow-lg border border-gray-100 p-8 max-w-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-Alumco-coral flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-display font-black text-Alumco-gray">Eliminar pregunta</p>
                        <p class="text-sm text-Alumco-gray/60">Esta acción no se puede deshacer</p>
                    </div>
                </div>
                <p class="text-sm text-Alumco-gray/70 mb-6">¿Estás seguro? Esta pregunta y todas sus alternativas serán eliminadas.</p>
                <div class="flex items-center gap-3">
                    <button wire:click="cancelarEliminar()" class="flex-1 px-4 py-2.5 text-sm font-medium text-Alumco-gray/60 hover:text-Alumco-gray border border-gray-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="confirmarEliminarPregunta()" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-Alumco-coral hover:bg-Alumco-coral/90 rounded-xl transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @elseif ($deletingType === 'opcion')
        <div class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:key="modal-opcion">
            <div class="bg-white rounded-[24px] shadow-lg border border-gray-100 p-8 max-w-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-Alumco-coral flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-display font-black text-Alumco-gray">Eliminar alternativa</p>
                        <p class="text-sm text-Alumco-gray/60">Esta acción no se puede deshacer</p>
                    </div>
                </div>
                <p class="text-sm text-Alumco-gray/70 mb-6">¿Estás seguro de que deseas eliminar esta alternativa?</p>
                <div class="flex items-center gap-3">
                    <button wire:click="cancelarEliminar()" class="flex-1 px-4 py-2.5 text-sm font-medium text-Alumco-gray/60 hover:text-Alumco-gray border border-gray-200 rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="confirmarEliminarOpcion()" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-Alumco-coral hover:bg-Alumco-coral/90 rounded-xl transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
    (function () {
        document.addEventListener('livewire:init', function () {
            // Flash auto-dismiss
            Livewire.on('flash-guardado', function () {
                clearTimeout(window._flashTimer);
                window._flashTimer = setTimeout(function () {
                    @this.set('flashMensaje', '');
                }, 3000);
            });

            // Focus new option input after agregarOpcion
            Livewire.on('opcion-agregada', function (params) {
                var preguntaId = params && params.preguntaId !== undefined
                    ? params.preguntaId
                    : (Array.isArray(params) && params[0] ? params[0].preguntaId : null);
                if (!preguntaId) return;
                setTimeout(function () {
                    var el = document.querySelector('[data-last-opcion="' + preguntaId + '"]');
                    if (el) el.focus();
                }, 80);
            });
        });

        // Tab on last option → add new option
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab' || e.shiftKey) return;
            var preguntaId = e.target.dataset && e.target.dataset.lastOpcion;
            if (!preguntaId) return;
            e.preventDefault();
            @this.call('agregarOpcion', parseInt(preguntaId));
        });

        // Drag-and-drop reorder
        var lista = document.getElementById('preguntas-lista');
        if (!lista) return;

        var dragging = null;
        var placeholder = document.createElement('div');
        placeholder.className = 'h-20 rounded-[24px] border-2 border-dashed border-Alumco-blue/20 bg-Alumco-blue/3';

        lista.addEventListener('dragstart', function (e) {
            var item = e.target.closest('.pregunta-item');
            if (!item) return;
            dragging = item;
            setTimeout(function () { item.classList.add('opacity-30'); }, 0);
            e.dataTransfer.effectAllowed = 'move';
        });

        lista.addEventListener('dragend', function () {
            if (!dragging) return;
            dragging.classList.remove('opacity-30');
            if (placeholder.parentNode) {
                placeholder.parentNode.replaceChild(dragging, placeholder);
            }
            var orden = Array.from(lista.querySelectorAll('.pregunta-item')).map(function (el) {
                return parseInt(el.dataset.preguntaId);
            });
            dragging = null;
            @this.call('reordenarPreguntas', orden);
        });

        lista.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragging) return;
            var after = getDragAfterElement(lista, e.clientY);
            if (after == null) {
                lista.appendChild(placeholder);
            } else {
                lista.insertBefore(placeholder, after);
            }
        });

        function getDragAfterElement(container, y) {
            var items = Array.from(container.querySelectorAll('.pregunta-item:not(.opacity-30)'));
            return items.reduce(function (closest, child) {
                var box = child.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                return offset < 0 && offset > closest.offset
                    ? { offset: offset, element: child }
                    : closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    })();
    </script>
</div>
