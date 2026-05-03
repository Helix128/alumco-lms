<div class="worker-calendar min-h-screen bg-Alumco-cream">

    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 space-y-8">

        {{-- ── Cabecera ──────────────────────────────────────────────────────── --}}
        <div class="worker-soft-panel px-4 py-5 sm:px-6 flex flex-wrap items-center justify-between gap-4">

            {{-- Navegación mes --}}
            <button
                wire:click="mesAnterior"
                class="flex items-center gap-2 px-3 py-2 sm:px-4 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition text-sm sm:text-base font-medium order-2 sm:order-none"
                aria-label="Mes anterior"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="hidden sm:inline">Anterior</span>
            </button>

            <div class="text-center w-full sm:w-auto order-1 sm:order-none">
                <h1 class="text-xl sm:text-3xl font-bold text-Alumco-gray capitalize">
                    @php
                        $nombresMes = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                    @endphp
                    {{ ucfirst($nombresMes[$mesActual - 1]) }} {{ $anioActual }}
                </h1>
                <button
                    wire:click="irAHoy"
                    class="mt-1 text-sm text-Alumco-blue underline hover:text-blue-800 transition"
                >
                    Ir a hoy
                </button>
            </div>

            <button
                wire:click="mesSiguiente"
                class="flex items-center gap-2 px-3 py-2 sm:px-4 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition text-sm sm:text-base font-medium order-3 sm:order-none"
                aria-label="Mes siguiente"
            >
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

        </div>

        {{-- ── Grid mensual ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden overflow-x-auto">
            <div class="min-w-[600px]">
                {{-- Cabecera días de la semana --}}
                <div class="grid grid-cols-7 border-b border-gray-200">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
                        <div class="py-3 text-center text-sm font-semibold text-gray-500 uppercase tracking-wide">
                            {{ $dia }}
                        </div>
                    @endforeach
                </div>

                {{-- Semanas --}}
                <div class="divide-y divide-gray-100">
                    @foreach($semanasDelMes as $semana)
                        <div class="grid grid-cols-7">
                            @foreach($semana['dias'] as $dia)
                                <div @class([
                                    'min-h-[80px] p-1.5 border-r border-gray-100 last:border-r-0',
                                    'bg-gray-50' => !$dia['esMesActual'] || $dia['esPasado'],
                                    'bg-white'   => $dia['esMesActual'] && !$dia['esPasado'],
                                ])>

                                    {{-- Número de día --}}
                                    <div class="flex justify-end mb-1">
                                        <span @class([
                                            'inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-semibold',
                                            'bg-Alumco-blue text-white' => $dia['esHoy'],
                                            'text-gray-300' => !$dia['esMesActual'],
                                            'text-gray-400' => $dia['esMesActual'] && $dia['esPasado'] && !$dia['esHoy'],
                                            'text-Alumco-gray' => $dia['esMesActual'] && !$dia['esPasado'] && !$dia['esHoy'],
                                        ])>
                                            {{ $dia['num'] }}
                                        </span>
                                    </div>

                                    {{-- Chips de cursos --}}
                                    <div class="space-y-0.5">
                                        @foreach(array_slice($dia['cursos'], 0, 2) as $curso)
                                            <div class="rounded px-1 py-0.5 {{ $curso['bg'] }} truncate text-white text-[10px] sm:text-xs leading-tight" title="{{ $curso['titulo'] }}">
                                                {{ $curso['titulo'] }}
                                            </div>
                                        @endforeach
                                        @if(count($dia['cursos']) > 2)
                                            <div class="text-[10px] text-gray-500 pl-1">+{{ count($dia['cursos']) - 2 }} más</div>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Cursos activos / del mes ──────────────────────────────────── --}}
        <div>
            <h2 class="text-xl font-bold text-Alumco-gray mb-4 flex items-center gap-2">
                <span class="inline-block w-2 h-6 bg-Alumco-green-vivid rounded-full"></span>
                Capacitaciones de este mes
            </h2>

            @if(count($cursosDelMes) > 0)
                <div class="space-y-3">
                    @foreach($cursosDelMes as $item)
                        <div class="bg-white rounded-xl shadow-sm border-l-4 {{ $item['border'] }} p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 mt-1.5 rounded-full {{ $item['bg'] }} shrink-0"></div>
                                <div>
                                    <p class="text-base font-semibold text-Alumco-gray">{{ $item['titulo'] }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Del {{ $item['inicio_texto'] }}<br>
                                        al {{ $item['fin_texto'] }}
                                    </p>
                                    @if($item['sede_nombre'])
                                        <p class="text-xs text-gray-400 mt-1">Sede: {{ $item['sede_nombre'] }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($item['activo'])
                                <span class="self-start sm:self-center shrink-0 inline-flex items-center gap-1 bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                    En curso
                                </span>
                            @else
                                <span class="self-start sm:self-center shrink-0 inline-flex items-center gap-1 bg-blue-50 text-Alumco-blue text-sm font-medium px-3 py-1 rounded-full">
                                    Próximamente
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 text-base">No hay capacitaciones programadas para este mes.</p>
                    <p class="text-gray-400 text-sm mt-1">Revisa los próximos meses usando las flechas de arriba.</p>
                </div>
            @endif
        </div>

        {{-- ── Próximas capacitaciones (hasta 60 días) ─────────────────── --}}
        @if(count($proximosCursos) > 0)
            <div>
                <h2 class="text-xl font-bold text-Alumco-gray mb-4 flex items-center gap-2">
                    <span class="inline-block w-2 h-6 bg-Alumco-blue rounded-full"></span>
                    Próximamente
                </h2>

                <div class="space-y-3">
                    @foreach($proximosCursos as $item)
                        <div class="bg-white rounded-xl shadow-sm border-l-4 {{ $item['border'] }} p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 mt-1.5 rounded-full {{ $item['bg'] }} shrink-0"></div>
                                <div>
                                    <p class="text-base font-semibold text-Alumco-gray">{{ $item['titulo'] }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Inicia el {{ $item['inicio_texto'] }}
                                        @if($item['dias_restantes'] <= 7)
                                            <span class="ml-2 text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">
                                                en {{ $item['dias_restantes'] }} {{ $item['dias_restantes'] === 1 ? 'día' : 'días' }}
                                            </span>
                                        @endif
                                    </p>
                                    @if($item['sede_nombre'])
                                        <p class="text-xs text-gray-400 mt-1">Sede: {{ $item['sede_nombre'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
