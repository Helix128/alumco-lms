<div>
    @if ($bloqueada)
        <div class="worker-card mx-auto max-w-2xl px-5 py-12 text-center lg:px-8">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-Alumco-coral/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-Alumco-coral-accessible" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2Zm-3 0H9V6a3 3 0 0 1 6 0v2Z"/>
                </svg>
            </div>
            <h2 class="font-display text-3xl font-black text-Alumco-coral-accessible">Límite de intentos alcanzado</h2>
            <p class="mx-auto mt-3 max-w-lg text-lg leading-relaxed text-Alumco-gray/75">
                Has alcanzado el máximo de intentos semanales para esta evaluación. Podrás intentarlo de nuevo en los próximos días.
            </p>
            <a href="{{ route('cursos.show', $curso) }}"
               class="worker-focus mt-7 inline-flex rounded-full bg-Alumco-blue px-8 py-4 text-lg font-black text-white shadow-sm">
                Volver a la capacitación
            </a>
        </div>

    @elseif (!$finalizada)
        <section class="mx-auto max-w-[90rem]">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <aside class="worker-card p-5 lg:sticky lg:top-28 lg:order-2 lg:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3 lg:block">
                        <span class="block text-base font-black text-Alumco-gray">
                            Pregunta {{ $indiceActual + 1 }} de {{ $preguntas->count() }}
                        </span>
                        <span class="block text-sm font-bold text-Alumco-blue lg:mt-1">
                            {{ round((($indiceActual + 1) / $preguntas->count()) * 100) }}% completado
                        </span>
                    </div>
                    <div class="flex gap-1.5" aria-hidden="true">
                        @for ($i = 0; $i < $preguntas->count(); $i++)
                            <div class="h-2 flex-1 rounded-full transition-colors duration-200
                                        {{ $i < $indiceActual ? 'bg-Alumco-green-accessible' : ($i === $indiceActual ? 'bg-Alumco-blue' : 'bg-gray-200') }}"></div>
                        @endfor
                    </div>
                    <p class="mt-4 hidden text-sm font-semibold leading-relaxed text-Alumco-gray/65 lg:block">
                        Selecciona una respuesta para avanzar. Puedes volver a preguntas anteriores antes de finalizar.
                    </p>
                </aside>

                <div class="space-y-6 lg:order-1">
                    <div class="worker-card p-5 lg:p-7">
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:gap-5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-Alumco-blue
                                        font-display text-xl font-black text-white shadow-sm">
                                {{ $indiceActual + 1 }}
                            </div>
                            <p class="text-xl font-black leading-snug text-Alumco-gray lg:text-2xl">{{ $preguntaActual->enunciado }}</p>
                        </div>

                        <div class="space-y-3">
                            @foreach ($preguntaActual->opciones as $i => $opcion)
                                @php
                                    $letra = chr(ord('A') + $i);
                                    $seleccionada = ($respuestasSeleccionadas[$preguntaActual->id] ?? null) == $opcion->id;
                                @endphp
                                <button wire:key="opcion-{{ $preguntaActual->id }}-{{ $opcion->id }}"
                                        wire:click="seleccionarOpcion({{ $preguntaActual->id }}, {{ $opcion->id }})"
                                        class="eval-option worker-focus data-loading:opacity-70 w-full rounded-2xl border-2 p-4 text-left
                                               {{ $seleccionada
                                                   ? 'selected border-Alumco-blue bg-Alumco-blue text-white shadow-sm'
                                                   : 'border-gray-200 bg-white text-Alumco-gray hover:border-Alumco-blue/40 hover:bg-Alumco-blue/5' }}"
                                        aria-pressed="{{ $seleccionada ? 'true' : 'false' }}">
                                    <span class="flex items-start gap-4 sm:gap-5">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full font-display text-lg font-black
                                                     {{ $seleccionada ? 'bg-white text-Alumco-blue' : 'bg-Alumco-blue/10 text-Alumco-blue' }}">
                                            {{ $letra }}
                                        </span>
                                        <span class="min-w-0 pt-1 text-base font-bold leading-snug sm:text-lg">{{ $opcion->texto }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        @if ($indiceActual > 0)
                            <button wire:click="anterior"
                                    class="worker-focus worker-action rounded-full border-2 border-Alumco-blue/25 bg-white px-5 py-4 text-lg font-black text-Alumco-blue">
                                Anterior
                            </button>
                        @endif

                        <button wire:click="siguiente"
                                @if (!isset($respuestasSeleccionadas[$preguntaActual->id])) disabled @endif
                                class="btn-primary worker-focus worker-action rounded-full bg-Alumco-green-accessible px-5 py-4 text-lg font-black text-white shadow-sm
                                       disabled:cursor-not-allowed disabled:opacity-40 data-loading:opacity-70
                                       {{ $indiceActual > 0 ? 'sm:col-span-2' : 'sm:col-span-3' }}">
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        </section>

    @else
        <div class="worker-card mx-auto max-w-2xl px-5 py-12 text-center lg:px-8" aria-live="polite">
            <div class="mb-3 inline-flex h-24 w-24 items-center justify-center rounded-full
                        {{ $aprobado ? 'bg-Alumco-green-accessible/10' : 'bg-Alumco-coral/10' }} mx-auto">
                <span class="font-display text-3xl font-black {{ $aprobado ? 'text-Alumco-green-accessible' : 'text-Alumco-coral-accessible' }}">
                    {{ $puntaje }}/{{ $preguntas->count() }}
                </span>
            </div>

            <h2 class="font-display text-3xl font-black {{ $aprobado ? 'text-Alumco-green-accessible' : 'text-Alumco-coral-accessible' }}">
                {{ $aprobado ? 'Aprobado' : 'No aprobado' }}
            </h2>

            <p class="mx-auto mt-3 max-w-lg text-lg leading-relaxed text-Alumco-gray/75">
                Respondiste <strong>{{ $puntaje }}</strong> de <strong>{{ $preguntas->count() }}</strong>
                preguntas correctamente.
            </p>

            @if ($certificadoGenerado)
                <div class="mx-auto mt-6 inline-flex items-center gap-2 rounded-full border border-Alumco-green-accessible/35 bg-Alumco-green/45
                            px-5 py-3 text-base font-bold text-Alumco-gray">
                    <svg class="h-5 w-5 text-Alumco-green-accessible" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 0 1 1.946-.806 3.42 3.42 0 0 1 4.438 0 3.42 3.42 0 0 0 1.946.806 3.42 3.42 0 0 1 3.138 3.138 3.42 3.42 0 0 0 .806 1.946 3.42 3.42 0 0 1 0 4.438 3.42 3.42 0 0 0-.806 1.946 3.42 3.42 0 0 1-3.138 3.138 3.42 3.42 0 0 0-1.946.806 3.42 3.42 0 0 1-4.438 0 3.42 3.42 0 0 0-1.946-.806 3.42 3.42 0 0 1-3.138-3.138 3.42 3.42 0 0 0-.806-1.946 3.42 3.42 0 0 1 0-4.438 3.42 3.42 0 0 0 .806-1.946 3.42 3.42 0 0 1 3.138-3.138Z"/>
                    </svg>
                    Certificado generado
                </div>
            @endif

            <div class="mt-7">
                <a href="{{ route('cursos.show', $curso) }}"
                   class="worker-focus inline-flex rounded-full bg-Alumco-blue px-8 py-4 text-lg font-black text-white shadow-sm">
                    Volver a la capacitación
                </a>
            </div>

            @if (!$aprobado)
                @if ($intentosRestantes > 0)
                    <p class="mt-5 text-base font-semibold text-Alumco-gray/70">
                        Te {{ $intentosRestantes === 1 ? 'queda' : 'quedan' }}
                        {{ $intentosRestantes }}
                        intento{{ $intentosRestantes > 1 ? 's' : '' }} disponible{{ $intentosRestantes > 1 ? 's' : '' }} esta semana.
                    </p>
                @else
                    <p class="mt-5 text-base font-bold text-Alumco-coral-accessible">
                        Has agotado tus intentos por esta semana.
                    </p>
                @endif
            @endif
        </div>
    @endif
</div>
