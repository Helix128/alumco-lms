<div>
    @if ($bloqueada)
        {{-- Pantalla de bloqueo --}}
        <div class="text-center py-10">
            <div class="text-6xl mb-4">🔒</div>
            <p class="text-2xl font-black text-Alumco-coral mb-2">Límite de intentos alcanzado</p>
            <p class="text-Alumco-gray mb-6">
                Has alcanzado el máximo de intentos semanales para esta evaluación.<br>
                Podrás intentarlo de nuevo en los próximos días.
            </p>
            <a href="{{ route('cursos.show', $curso) }}"
               class="inline-block bg-Alumco-blue text-white font-bold py-3 px-10 rounded-2xl shadow-md">
                Volver al curso
            </a>
        </div>

    @elseif (!$finalizada)
        <style>#app-bottom-nav { display: none !important; }</style>

        {{-- Barra de progreso --}}
        <div class="flex items-center gap-3 mb-6">
            <span class="font-bold text-Alumco-gray whitespace-nowrap text-sm">
                Pregunta {{ $indiceActual + 1 }} de {{ $preguntas->count() }}
            </span>
            <div class="flex-1 bg-gray-200 rounded-full h-3">
                <div class="h-3 bg-Alumco-green-vivid rounded-full transition-all duration-300"
                     style="width: {{ round((($indiceActual + 1) / $preguntas->count()) * 100) }}%">
                </div>
            </div>
        </div>

        {{-- Enunciado --}}
        <div class="flex items-start gap-3 mb-6">
            <div class="bg-Alumco-blue text-white rounded-full w-10 h-10 flex items-center justify-center
                        font-black text-lg shrink-0 shadow-sm">
                {{ $indiceActual + 1 }}
            </div>
            <p class="font-bold text-lg text-Alumco-gray leading-snug">{{ $preguntaActual->enunciado }}</p>
        </div>

        {{-- Opciones --}}
        @foreach ($preguntaActual->opciones as $i => $opcion)
            @php
                $letra       = chr(ord('a') + $i);
                $seleccionada = ($respuestasSeleccionadas[$preguntaActual->id] ?? null) == $opcion->id;
            @endphp
            <button wire:click="seleccionarOpcion({{ $preguntaActual->id }}, {{ $opcion->id }})"
                    class="eval-option w-full flex items-center gap-4 mb-3 rounded-2xl border-2 p-4 text-left
                           cursor-pointer
                           {{ $seleccionada
                               ? 'selected bg-Alumco-blue border-Alumco-blue text-white shadow-md'
                               : 'bg-white border-gray-200 text-Alumco-gray' }}">
                <div class="rounded-full w-10 h-10 flex items-center justify-center font-black text-base shrink-0
                            {{ $seleccionada
                                ? 'bg-white text-Alumco-blue'
                                : 'bg-Alumco-blue/15 text-Alumco-blue' }}">
                    {{ $letra }}
                </div>
                <span class="font-semibold leading-snug">{{ $opcion->texto }}</span>
            </button>
        @endforeach

        {{-- Botón Continuar --}}
        <button wire:click="siguiente"
                @if (!isset($respuestasSeleccionadas[$preguntaActual->id])) disabled @endif
                class="btn-primary w-full mt-5 bg-Alumco-green-vivid text-white text-xl font-black py-4 rounded-2xl
                       shadow-md
                       disabled:opacity-40 disabled:cursor-not-allowed">
            Continuar
        </button>

        {{-- Navegación anterior/siguiente (fija al fondo, sustituye bottom nav) --}}
        <div class="fixed bottom-0 inset-x-0 z-50 bg-Alumco-blue h-16 flex items-center justify-between px-10">
            {{-- Anterior --}}
            <button wire:click="anterior"
                    @if ($indiceActual === 0) disabled @endif
                    class="btn-round bg-white rounded-full w-12 h-12 flex items-center justify-center
                           shadow-md disabled:opacity-30 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-Alumco-blue" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Siguiente / Finalizar --}}
            <button wire:click="siguiente"
                    @if (!isset($respuestasSeleccionadas[$preguntaActual->id])) disabled @endif
                    class="btn-round bg-white rounded-full w-12 h-12 flex items-center justify-center
                           shadow-md disabled:opacity-30 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-Alumco-blue" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

    @else
        {{-- Pantalla de resultado --}}
        <div class="text-center py-10">
            <div class="text-8xl font-black mb-3
                        {{ $aprobado ? 'text-Alumco-green-vivid' : 'text-Alumco-coral' }}">
                {{ $puntaje }}/{{ $preguntas->count() }}
            </div>

            <p class="text-2xl font-black mb-2
                      {{ $aprobado ? 'text-Alumco-green-vivid' : 'text-Alumco-coral' }}">
                {{ $aprobado ? '¡Aprobado!' : 'No aprobado' }}
            </p>

            <p class="text-Alumco-gray mb-6">
                Respondiste <strong>{{ $puntaje }}</strong> de <strong>{{ $preguntas->count() }}</strong>
                preguntas correctamente.
            </p>

            @if ($certificadoGenerado)
                <div class="inline-flex items-center gap-2 bg-Alumco-green/50 border border-Alumco-green-vivid
                            text-Alumco-gray font-semibold px-5 py-2.5 rounded-2xl mb-6">
                    <svg class="w-5 h-5 text-Alumco-green-vivid" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    ¡Certificado generado!
                </div>
            @endif

            <div class="flex flex-col gap-3 items-center">
                <a href="{{ route('cursos.show', $curso) }}"
                   class="btn-primary inline-block bg-Alumco-blue text-white font-bold py-3 px-10 rounded-2xl
                          shadow-md">
                    Volver al curso
                </a>
            </div>

            @if (!$aprobado)
                @if ($intentosRestantes > 0)
                    <p class="text-sm text-Alumco-gray/60 mt-4">
                        Te {{ $intentosRestantes === 1 ? 'queda' : 'quedan' }}
                        {{ $intentosRestantes }}
                        intento{{ $intentosRestantes > 1 ? 's' : '' }} disponible{{ $intentosRestantes > 1 ? 's' : '' }} esta semana.
                    </p>
                @else
                    <p class="text-sm text-Alumco-coral mt-4">
                        Has agotado tus intentos por esta semana.
                    </p>
                @endif
            @endif
        </div>
    @endif
</div>
