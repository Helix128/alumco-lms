{{--
    Tailwind safelist — dynamic course-palette classes (do not remove):
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div id="cal-root"
     data-plan-mode="{{ $modoPlaneacion ? '1' : '0' }}"
     data-vista="{{ $modoVista }}"
     data-days-in-month="{{ $diasEnMes }}"
     data-sexo="{{ $userSexo }}"
     @class(['p-3 relative', 'plan-mode-active' => $modoPlaneacion, 'cal-mode-readonly' => $readonly])>

    {{-- Floating tooltip during drag / resize / move (shown/hidden by JS) --}}
    <div id="cal-tooltip" class="cal-drag-tooltip" aria-hidden="true"></div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-3 bg-white p-3 rounded-xl shadow-sm border border-gray-200 gap-4 flex-wrap">

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
            @if($modoVista === 'anual')
                {{-- Year navigation --}}
                <button wire:click="irAnioAnterior"
                        wire:loading.attr="disabled"
                        wire:target="irAnioAnterior,irAnioSiguiente"
                        title="Año anterior"
                        class="w-9 h-9 flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-bold nav-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <h2 class="text-xl font-bold text-gray-800 min-w-20 text-center"
                    wire:loading.class="opacity-50"
                    wire:target="irAnioAnterior,irAnioSiguiente">
                    {{ $anioActual }}
                </h2>

                <button wire:click="irAnioSiguiente"
                        wire:loading.attr="disabled"
                        wire:target="irAnioAnterior,irAnioSiguiente"
                        title="Año siguiente"
                        class="w-9 h-9 flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-bold nav-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                @if(! $esAnioActual)
                    <button wire:click="irAHoy"
                            wire:loading.attr="disabled"
                            wire:target="irAnioAnterior,irAnioSiguiente,irAHoy"
                            title="Ir al año actual"
                            class="px-3 h-9 flex items-center text-sm font-semibold text-Alumco-blue border border-Alumco-blue rounded-lg nav-btn">
                        Hoy
                    </button>
                @endif
            @else
                {{-- Month navigation --}}
                <button wire:click="mesAnterior"
                        wire:loading.attr="disabled"
                        wire:target="mesAnterior,mesSiguiente,irAHoy"
                        title="Mes anterior (←)"
                        class="w-9 h-9 flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-bold nav-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                @if(! $esMesActual)
                    <button wire:click="irAHoy"
                            wire:loading.attr="disabled"
                            wire:target="mesAnterior,mesSiguiente,irAHoy"
                            title="Ir a hoy"
                            class="px-3 h-9 flex items-center text-sm font-semibold text-Alumco-blue border border-Alumco-blue rounded-lg nav-btn">
                        Hoy
                    </button>
                @endif

                <button wire:click="mesSiguiente"
                        wire:loading.attr="disabled"
                        wire:target="mesAnterior,mesSiguiente,irAHoy"
                        title="Mes siguiente (→)"
                        class="w-9 h-9 flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-bold nav-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <h2 class="text-xl font-bold text-gray-800 ml-1 min-w-36"
                    wire:loading.class="opacity-50"
                    wire:target="mesAnterior,mesSiguiente,irAHoy">
                    {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }}
                    {{ $anioActual }}
                </h2>
            @endif
        </div>

        {{-- Right controls: view toggle + sede filter + plan mode --}}
        <div class="flex items-center gap-2 flex-wrap">

            {{-- Vista toggle --}}
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg">
                <button wire:click="cambiarVista('anual')"
                        @class(['cal-view-tab', 'cal-view-tab-active' => $modoVista === 'anual'])>
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                    </svg>
                    Anual
                </button>
                <button wire:click="cambiarVista('mensual')"
                        @class(['cal-view-tab', 'cal-view-tab-active' => $modoVista === 'mensual'])>
                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Mensual
                </button>
            </div>

            @if($esAdmin && $modoVista === 'anual')
                <button wire:click="abrirModalCopiarAnio"
                        class="px-3 h-9 flex items-center text-sm font-semibold text-Alumco-blue border border-Alumco-blue rounded-lg nav-btn gap-1.5"
                        title="Copiar planificaciones a otro año">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2"/>
                    </svg>
                    Copiar año
                </button>
            @endif

            @if($esAdmin && $modoVista === 'anual')
                <button wire:click="toggleModoPlaneacion"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg border font-semibold text-sm plan-toggle-btn
                               {{ $modoPlaneacion
                                   ? 'bg-Alumco-blue text-white border-Alumco-blue'
                                   : 'bg-white text-Alumco-blue border-Alumco-blue' }}">
                    @if($modoPlaneacion)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Salir de planificación
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Planificar
                    @endif
                </button>
            @endif
        </div>
    </div>

    {{-- ── Planning hint banner ────────────────────────────────────────── --}}
    @if($modoPlaneacion)
        <div class="mb-2 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                <strong>Modo planificaci&oacute;n activo.</strong>
                Haz clic en un bloque para editarlo; arr&aacute;stralo para moverlo (incluso entre filas/sedes),
                o arrastra sus bordes para ajustar inicio y fin.
                Al pasar el cursor sobre un bloque aparece la <strong>&times;</strong> para eliminarlo.
                Presiona <kbd class="px-1 py-0.5 bg-blue-100 rounded text-xs font-mono">Esc</kbd> para salir.
            </span>
        </div>
    @endif

    {{-- ── Main layout: calendar + optional sidebar ──────────────────── --}}
    <div class="flex gap-4 items-start">

        <div class="flex-1 min-w-0">

            {{-- ════════════════════════════════════════════════════════ --}}
            {{--  VISTA ANUAL                                            --}}
            {{-- ════════════════════════════════════════════════════════ --}}
            @if($modoVista === 'anual')

                {{-- ── Conflict warning panel ─────────────────────────────── --}}
                @php
                    $todosConflictos = [];
                    foreach ($filasAnuales as $fila) {
                        foreach ($semanasDelAnio as $sem) {
                            $sd = $fila['semanas'][$sem['numero']] ?? null;
                            if ($sd && $sd['conflicto']) {
                                $todosConflictos[] = [
                                    'sede_nombre' => $fila['nombre'],
                                    'numero'      => $sem['numero'],
                                    'inicio'      => $sem['inicio'],
                                    'fin'         => $sem['fin'],
                                    'cursos'      => collect($sd['cursos'])->unique('id')->values()->all(),
                                ];
                            }
                        }
                    }
                @endphp
                @if($esAdmin && count($todosConflictos) > 0)
                    <details open class="mb-2 rounded-xl border border-amber-200 bg-amber-50 overflow-hidden">
                        <summary class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-amber-700 cursor-pointer list-none select-none">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            {{ count($todosConflictos) }} semana{{ count($todosConflictos) > 1 ? 's' : '' }} con solapamiento
                            <svg class="w-3.5 h-3.5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-4 pb-3 pt-1 space-y-1.5 border-t border-amber-200">
                            @foreach($todosConflictos as $conf)
                                <div class="flex items-start gap-2 text-xs">
                                    <span class="font-bold text-amber-800 shrink-0 w-8">S{{ $conf['numero'] }}</span>
                                    <span class="text-amber-600 shrink-0 font-semibold">{{ $conf['sede_nombre'] }}</span>
                                    <span class="text-amber-500 shrink-0">
                                        {{ \Carbon\Carbon::parse($conf['inicio'])->format('d/m') }}–{{ \Carbon\Carbon::parse($conf['fin'])->format('d/m') }}
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($conf['cursos'] as $c)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-white text-[10px] font-semibold {{ $c['bg'] }}">
                                                {{ \Illuminate\Support\Str::limit($c['titulo'], 28) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif

                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
                    <div class="cal-annual-scroll">
                        <div class="cal-annual-grid"
                             style="--cal-n-semanas: {{ $nSemanas }}">

                            {{-- ── Fila 1: Mes headers ──────────────── --}}
                            <div class="cal-annual-corner">
                                <span class="text-gray-400 text-[10px]">Mes</span>
                            </div>
                            @foreach($mesesDelAnio as $mesIdx => $mes)
                                <div class="cal-annual-month-header {{ $mesIdx % 2 === 0 ? 'cal-annual-month-even' : 'cal-annual-month-odd' }}"
                                     style="grid-column: span {{ $mes['span'] }}">
                                    {{ $mes['nombre'] }}
                                </div>
                            @endforeach

                            {{-- ── Fila 2: Número de semana ──────────── --}}
                            <div class="cal-annual-corner">
                                <span class="text-gray-400 text-[10px]">Sem.</span>
                            </div>
                            @foreach($semanasDelAnio as $sem)
                                <div @class([
                                    'cal-annual-week-num',
                                    'cal-annual-week-today' => $sem['esHoy'],
                                ])>
                                    {{ $sem['numero'] }}
                                </div>
                            @endforeach

                            {{-- ── Fila 3: Rango de fechas ───────────── --}}
                            <div class="cal-annual-corner">
                                <span class="text-gray-400 text-[10px]">Fecha</span>
                            </div>
                            @foreach($semanasDelAnio as $sem)
                                <div class="cal-annual-week-dates">
                                    {{ \Carbon\Carbon::parse($sem['inicio'])->format('d') }}<br>
                                    <span class="text-gray-300">{{ \Carbon\Carbon::parse($sem['fin'])->format('d') }}</span>
                                </div>
                            @endforeach

                            {{-- ── Filas: una por sede ─────────────────── --}}
                            {{-- En vista de colaborador solo se muestran la fila global y la sede propia --}}
                            @foreach($filasAnuales as $fila)
                                @if($readonly && $fila['sede_id'] !== null && $fila['sede_id'] !== $userSedeId)
                                    @continue
                                @endif

                                {{-- Row label (sticky) --}}
                                <div class="cal-annual-row-label">
                                    @if($fila['sede_id'] === null)
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    @endif
                                    <span class="truncate">{{ $fila['nombre'] }}</span>
                                </div>

                                {{-- Week cells for this sede --}}
                                @foreach($semanasDelAnio as $sem)
                                    @php
                                        $semData     = $fila['semanas'][$sem['numero']] ?? ['cursos' => [], 'conflicto' => false];
                                        $tieneCursos = count($semData['cursos']) > 0;
                                    @endphp
                                    <div @class([
                                             'cal-annual-cell',
                                             'cal-annual-cell-empty'    => ! $tieneCursos,
                                             'cal-annual-cell-conflict' => ! $readonly && $semData['conflicto'],
                                             'cal-annual-cell-past'     => $sem['esPasada'] && ! $sem['esHoy'],
                                             'cal-annual-cell-today'    => $sem['esHoy'],
                                         ])
                                         data-semana="{{ $sem['numero'] }}"
                                         data-sede-id="{{ $fila['sede_id'] ?? 0 }}"
                                         data-fecha-inicio="{{ $sem['inicio'] }}"
                                         data-fecha-fin="{{ $sem['fin'] }}">

                                        @foreach($semData['cursos'] as $curso)
                                            <div @class([
                                                    'cal-annual-chip',
                                                    $curso['bg'],
                                                    'rounded-l-md' => $curso['esInicio'],
                                                    'rounded-r-md' => $curso['esFin'],
                                                 ])
                                                 data-bar-anual="{{ $curso['id'] }}"
                                                 wire:key="chip-{{ $fila['sede_id'] ?? 0 }}-{{ $sem['numero'] }}-{{ $curso['id'] }}"
                                                 title="{{ $curso['titulo'] }}{{ $curso['notas'] ? ' — '.$curso['notas'] : '' }}">

                                                {{-- Left resize handle (plan mode, start cell only) --}}
                                                @if($esAdmin && $modoPlaneacion && $curso['esInicio'])
                                                    <button type="button"
                                                            data-ann-resize="left"
                                                            data-semana="{{ $curso['semaInicio'] }}"
                                                            title="Mover inicio"></button>
                                                @endif

                                                {{-- Move zone + label --}}
                                                @if($esAdmin && $modoPlaneacion)
                                                    <div data-ann-move
                                                         data-semana="{{ $curso['semaInicio'] }}"
                                                         data-duration="{{ $curso['semaFin'] - $curso['semaInicio'] }}">
                                                        @if($curso['esInicio'])
                                                            <span class="cal-annual-chip-label text-white text-[11px] font-semibold px-1.5 truncate leading-none">
                                                                {{ $curso['titulo'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif($curso['esInicio'])
                                                    <span class="cal-annual-chip-label text-white text-[11px] font-semibold px-1.5 truncate leading-none">
                                                        {{ $curso['titulo'] }}
                                                    </span>
                                                @endif

                                                {{-- Delete button: visible on chip hover; confirm required --}}
                                                @if($esAdmin && $curso['esFin'])
                                                    <button type="button"
                                                            data-ann-delete="{{ $curso['id'] }}"
                                                            class="cal-annual-chip-delete text-white/80 hover:text-white shrink-0 px-1 leading-none"
                                                            title="Eliminar">
                                                        &times;
                                                    </button>
                                                @endif

                                                {{-- Right resize handle (plan mode, end cell only) --}}
                                                @if($esAdmin && $modoPlaneacion && $curso['esFin'])
                                                    <button type="button"
                                                            data-ann-resize="right"
                                                            data-semana="{{ $curso['semaFin'] }}"
                                                            title="Mover fin"></button>
                                                @endif
                                            </div>
                                        @endforeach

                                        {{-- Spacer: siempre hay espacio clickeable al final --}}
                                        <div style="flex:1;min-height:10px"></div>

                                        @if(! $readonly && $semData['conflicto'])
                                            <div class="cal-annual-conflict-badge" title="{{ count($semData['cursos']) }} cursos en esta semana">!</div>
                                        @endif
                                    </div>
                                @endforeach

                            @endforeach

                        </div>{{-- /.cal-annual-grid --}}
                    </div>{{-- /.cal-annual-scroll --}}
                </div>

            {{-- ════════════════════════════════════════════════════════ --}}
            {{--  VISTA MENSUAL                                          --}}
            {{-- ════════════════════════════════════════════════════════ --}}
            @else
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">

                    {{-- Day-of-week header --}}
                    <div class="cal-header">
                        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dowIdx => $dow)
                            <div class="py-3 text-center text-xs font-bold text-gray-500
                                        {{ $dowIdx >= 5 ? 'text-gray-400' : '' }}">
                                {{ $dow }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Week rows --}}
                    @foreach($semanasDelMes as $semIdx => $semana)
                        <div class="cal-week" style="grid-template-rows: auto repeat({{ max($semana['maxSlot'], 1) }}, 26px) 4px">

                            {{-- Day number cells --}}
                            @foreach($semana['dias'] as $dIdx => $diaInfo)
                                <div @class([
                                         'cal-day-cell',
                                         'cal-day-today'   => $diaInfo['esHoy'],
                                         'cal-day-outside' => ! $diaInfo['esMesActual'],
                                         'cal-day-weekend' => $diaInfo['esWeekend'] && $diaInfo['esMesActual'],
                                     ])
                                     style="grid-column:{{ $dIdx + 1 }};grid-row:1"
                                     data-col="{{ $dIdx + 1 }}"
                                     @if($diaInfo['esMesActual']) data-day="{{ $diaInfo['num'] }}" @endif>
                                    <span @class([
                                        'text-white bg-Alumco-blue rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold' => $diaInfo['esHoy'],
                                        'text-sm font-bold text-gray-500' => ! $diaInfo['esHoy'],
                                    ])>
                                        {{ $diaInfo['num'] }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Course bars --}}
                            @foreach($semana['barras'] as $barra)
                                @php $esPrimerSegmento = $barra['roundLeft'] || $barra['extiendePorIzq']; @endphp
                                <div wire:key="bar-cal-{{ $semIdx }}-{{ $barra['id'] }}"
                                     @class([
                                         'cal-bar',
                                         $barra['bg'],
                                         'rounded-l-md'         => $barra['roundLeft'],
                                         'rounded-r-md'         => $barra['roundRight'],
                                         'cal-bar-continuation' => ! $esPrimerSegmento,
                                     ])
                                     style="grid-column: {{ $barra['col'] }} / span {{ $barra['span'] }}; grid-row: {{ $barra['slot'] + 2 }}"
                                     title="{{ $barra['titulo'] }} ({{ $barra['fechaIni'] }} &rarr; {{ $barra['fechaFin'] }}){{ $barra['notas'] ? ' — '.$barra['notas'] : '' }}"
                                     data-bar-id="{{ $barra['id'] }}">

                                    {{-- Left resize handle (planning mode only) --}}
                                    @if($esAdmin && $modoPlaneacion)
                                        <button type="button"
                                                class="bar-resize-handle bar-resize-left"
                                                title="Mover inicio"
                                                data-resize="left"
                                                data-day="{{ $barra['edgeStartDay'] }}"></button>
                                    @endif

                                    {{-- Left cross-month arrow --}}
                                    @if($barra['extiendePorIzq'])
                                        <span class="bar-arrow-left" title="Comienza antes de este mes">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @endif

                                    {{-- Draggable center / label zone --}}
                                    @if($esAdmin && $modoPlaneacion)
                                        <div class="bar-move-handle"
                                             data-move="1"
                                             data-span="{{ $barra['span'] }}"
                                             data-start="{{ $barra['segStartDay'] }}">
                                            @if($esPrimerSegmento)
                                                <span class="cal-bar-label">
                                                    {{ $barra['titulo'] }}
                                                    @if($barra['sede_nombre'])
                                                        <span class="cal-sede-badge">{{ $barra['sede_nombre'] }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($esPrimerSegmento)
                                        <span class="cal-bar-label">
                                            {{ $barra['titulo'] }}
                                            @if($barra['sede_nombre'])
                                                <span class="cal-sede-badge">{{ $barra['sede_nombre'] }}</span>
                                            @endif
                                        </span>
                                    @endif

                                    {{-- Right cross-month arrow --}}
                                    @if($barra['extiendePorDer'])
                                        <span class="bar-arrow-right" title="Contin&uacute;a en el siguiente mes">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @endif

                                    {{-- Delete button: confirm required --}}
                                    @if($esAdmin)
                                        <button type="button"
                                                data-bar-delete="{{ $barra['id'] }}"
                                                class="cal-bar-delete"
                                                title="Eliminar periodo">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Right resize handle (planning mode only) --}}
                                    @if($esAdmin && $modoPlaneacion)
                                        <button type="button"
                                                class="bar-resize-handle bar-resize-right"
                                                title="Mover fin"
                                                data-resize="right"
                                                data-day="{{ $barra['edgeEndDay'] }}"></button>
                                    @endif
                                </div>
                            @endforeach


        </div>{{-- /.flex-1 --}}

    </div>{{-- /main layout --}}

    {{-- ── Planning modal ─────────────────────────────────────────────── --}}
    @if($mostrarModalPlanificacion)
        <div id="planning-modal"
             class="fixed inset-0 bg-black/50 z-50 flex justify-center items-center backdrop-blur-sm"
             wire:click="cerrarModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden mx-4"
                 onclick="event.stopPropagation()">

                {{-- Modal header --}}
                <div class="bg-Alumco-blue px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg">
                        {{ $editandoId ? 'Editar periodo' : 'Planificar periodo de curso' }}
                    </h3>
                    <button wire:click="cerrarModal"
                            class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
                </div>

                {{-- Modal body --}}
                <div class="p-5 space-y-4">

                    {{-- Course search + picker --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Curso</label>

                        <div class="modal-course-search">
                            <svg class="modal-course-search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            <input type="text"
                                   wire:model.live.debounce.200ms="queryModal"
                                   placeholder="Buscar curso..."
                                   autocomplete="off">
                        </div>

                        <div class="modal-course-list">
                            @forelse($modalList as $curso)
                                <div @class(['modal-course-option', 'selected' => $cursoId == $curso['id']])
                                     wire:click="seleccionarCurso({{ $curso['id'] }})"
                                     wire:key="modal-opt-{{ $curso['id'] }}">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $curso['bg'] }}"></span>
                                    <span>{{ $curso['titulo'] }}</span>
                                    @if($cursoId == $curso['id'])
                                        <svg class="w-4 h-4 ml-auto text-Alumco-blue shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-3">Sin resultados</p>
                            @endforelse
                        </div>

                        @error('cursoId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Period selector: weeks (annual) or dates (monthly) --}}
                    @if($modoVista === 'anual')
                        {{-- Semana inicio --}}
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Semana de inicio</label>
                                <select wire:model.live="semanaInicioPlan"
                                        class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                                    @foreach($semanasDelAnio as $sem)
                                        <option value="{{ $sem['numero'] }}">
                                            S{{ $sem['numero'] }}
                                            — {{ \Carbon\Carbon::parse($sem['inicio'])->locale('es')->isoFormat('D MMM') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Semana de fin</label>
                                <select wire:model.live="semanaFinPlan"
                                        class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                                    @foreach($semanasDelAnio as $sem)
                                        <option value="{{ $sem['numero'] }}">
                                            S{{ $sem['numero'] }}
                                            — {{ \Carbon\Carbon::parse($sem['fin'])->locale('es')->isoFormat('D MMM') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Preview of the resolved dates --}}
                        @if($fechaInicioPlan && $fechaFinPlan)
                            <p class="text-xs text-gray-500 -mt-2">
                                <svg class="w-3.5 h-3.5 inline-block text-gray-400 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse($fechaInicioPlan)->locale('es')->isoFormat('D [de] MMMM') }}
                                &rarr;
                                {{ \Carbon\Carbon::parse($fechaFinPlan)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                            </p>
                        @endif
                        @error('fechaInicioPlan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('fechaFinPlan')    <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @else
                        {{-- Date range (vista mensual) --}}
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                                <input type="date" wire:model="fechaInicioPlan"
                                       class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                                @error('fechaInicioPlan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                                <input type="date" wire:model="fechaFinPlan"
                                       class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                                @error('fechaFinPlan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Notas <span class="text-gray-400 font-normal">(opcional)</span>
                        </label>
                        <textarea wire:model="notas" rows="2"
                                  placeholder="Ej: Grupo A &mdash; turno ma&ntilde;ana"
                                  class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue"></textarea>
                    </div>

                    {{-- Sede --}}
                    @if(count($sedes) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sede</label>
                            <select wire:model="sedeIdPlan"
                                    class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                                <option value="">Todas las sedes</option>
                                @foreach($sedes as $sede)
                                    <option value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>
                                @endforeach
                            </select>
                            @error('sedeIdPlan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                {{-- Modal footer --}}
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2 border-t">
                    <button wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium text-sm cancel-btn">
                        Cancelar
                    </button>
                    <button wire:click="guardarPlanificacion"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60"
                            class="px-4 py-2 bg-Alumco-blue text-white rounded-lg font-medium text-sm save-btn">
                        <span wire:loading.remove wire:target="guardarPlanificacion">
                            {{ $editandoId ? 'Actualizar' : 'Guardar periodo' }}
                        </span>
                        <span wire:loading wire:target="guardarPlanificacion">Guardando...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ── Modal copiar año ───────────────────────────────────────────── --}}
    @if($mostrarModalCopiarAnio)
        <div class="fixed inset-0 bg-black/50 z-50 flex justify-center items-center backdrop-blur-sm"
             wire:click="cerrarModalCopiarAnio">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden mx-4"
                 onclick="event.stopPropagation()">

                <div class="bg-Alumco-blue px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg">Copiar planificación</h3>
                    <button wire:click="cerrarModalCopiarAnio"
                            class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
                </div>

                <div class="p-5 space-y-4">
                    <p class="text-sm text-gray-600">
                        Se copiarán todas las planificaciones del año <strong>{{ $anioActual }}</strong>
                        al año seleccionado, manteniendo las mismas fechas de mes y día.
                    </p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Año destino</label>
                        <input type="number" wire:model="anioDestino" min="2020" max="2099"
                               class="w-full border border-gray-300 rounded-lg shadow-sm p-2 text-sm focus:outline-none focus:ring-1 focus:ring-Alumco-blue focus:border-Alumco-blue">
                        @error('anioDestino') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-2 border-t">
                    <button wire:click="cerrarModalCopiarAnio"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium text-sm">
                        Cancelar
                    </button>
                    <button wire:click="copiarAnio"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60"
                            class="px-4 py-2 bg-Alumco-blue text-white rounded-lg font-medium text-sm">
                        <span wire:loading.remove wire:target="copiarAnio">Copiar</span>
                        <span wire:loading wire:target="copiarAnio">Copiando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Confirm delete modal ──────────────────────────────────────────── --}}
    @if($esAdmin)
        <div id="confirm-delete-modal"
             class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden mx-4">
                <div class="bg-red-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-base">Confirmar eliminaci&oacute;n</h3>
                    <button id="confirm-delete-close"
                            class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-5">
                    <p id="confirm-delete-text" class="text-sm text-gray-700 mb-4">&iquest;Est&aacute;s seguro/a?</p>
                    <div class="flex justify-end gap-2">
                        <button id="confirm-delete-cancel"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium text-sm">
                            Cancelar
                        </button>
                        <button id="confirm-delete-ok"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium text-sm">
                            S&iacute;, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
(function () {
    'use strict';

    /* ════════════════════════════════════════════════════════════════════ */
    /*  UTILIDADES COMUNES                                                 */
    /* ════════════════════════════════════════════════════════════════════ */

    function getRoot()  { return document.getElementById('cal-root'); }
    function isPlanMode() { const r = getRoot(); return r && r.dataset.planMode === '1'; }
    function getVista()   { const r = getRoot(); return r ? r.dataset.vista : 'anual'; }
    function getWire()  {
        const r = getRoot();
        return r ? window.Livewire.find(r.getAttribute('wire:id')) : null;
    }

    const tip = document.getElementById('cal-tooltip');
    function showTip(text, x, y) {
        if (!tip) return;
        tip.textContent = text;
        tip.style.cssText = `left:${x + 14}px;top:${y - 32}px;display:block`;
    }
    function hideTip() { if (tip) tip.style.display = 'none'; }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  CONFIRM DELETE                                                     */
    /* ════════════════════════════════════════════════════════════════════ */

    let deletePendingId = null;

    function showConfirmDelete(id) {
        deletePendingId = id;
        const modal = document.getElementById('confirm-delete-modal');
        if (!modal) return;
        const r    = getRoot();
        const sexo = r ? (r.dataset.sexo || 'M') : 'M';
        const txt  = document.getElementById('confirm-delete-text');
        if (txt) {
            txt.innerHTML = sexo === 'F'
                ? '&iquest;Est&aacute;s segura de que quieres eliminar esta planificaci&oacute;n?'
                : '&iquest;Est&aacute;s seguro de que quieres eliminar esta planificaci&oacute;n?';
        }
        modal.classList.remove('hidden');
    }

    function initConfirmDelete() {
        const modal = document.getElementById('confirm-delete-modal');
        if (!modal) return;
        const close = () => { modal.classList.add('hidden'); deletePendingId = null; };
        document.getElementById('confirm-delete-close')?.addEventListener('click', close);
        document.getElementById('confirm-delete-cancel')?.addEventListener('click', close);
        modal.addEventListener('click', e => { if (e.target === modal) close(); });
        document.getElementById('confirm-delete-ok')?.addEventListener('click', () => {
            if (deletePendingId !== null) getWire()?.$call('borrarPlanificacion', deletePendingId);
            close();
        });
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  VISTA ANUAL — interactions                                         */
    /* ════════════════════════════════════════════════════════════════════ */

    let annualDragStart = null, annualDragEnd = null, annualDragging = false;
    let annDragSedeId = 0; /* sede_id (0 = Global/null) de la fila donde empezó el drag */
    /* resizing / moving in annual view */
    let annResizing = false, annResizeId = null, annResizeEdge = null, annResizeSemana = null, annResizeOrig = null;
    let annMoving = false, annMoveId = null, annMoveDuration = 0, annMoveSemana = null, annMoveOrig = null;
    let annMoveTargetSedeId = 0, annMoveOrigSedeId = 0; /* sede destino/origen al mover entre filas */
    let annWasDragging = false;

    function annClearHighlight() {
        document.querySelectorAll('.cal-annual-cell-drag-highlight').forEach(el =>
            el.classList.remove('cal-annual-cell-drag-highlight'));
    }

    /* Highlight cells for drag-to-create (range in same sede row) */
    function annUpdateHighlight() {
        if (annualDragStart === null || annualDragEnd === null) return;
        const lo = Math.min(annualDragStart, annualDragEnd);
        const hi = Math.max(annualDragStart, annualDragEnd);
        document.querySelectorAll('.cal-annual-cell').forEach(el => {
            const s        = parseInt(el.dataset.semana, 10);
            const sameSede = parseInt(el.dataset.sedeId || '0', 10) === annDragSedeId;
            el.classList.toggle('cal-annual-cell-drag-highlight', s >= lo && s <= hi && sameSede);
        });
    }

    /* Highlight cells for a move operation (shows where the chip will land) */
    function annMoveHighlight(semIni, duracion, sedeId) {
        annClearHighlight();
        const lo = semIni, hi = semIni + duracion;
        document.querySelectorAll('.cal-annual-cell').forEach(el => {
            const s    = parseInt(el.dataset.semana, 10);
            const sede = parseInt(el.dataset.sedeId || '0', 10);
            el.classList.toggle('cal-annual-cell-drag-highlight', s >= lo && s <= hi && sede === sedeId);
        });
    }

    /* Resolves the target .cal-annual-cell from a mouse event.
       Must use .cal-annual-cell — NOT [data-semana] — because chip inner
       elements (move handle, resize handles) also carry data-semana attributes
       and would be matched first by closest(), returning wrong week numbers. */
    function resolveAnnCell(e) {
        const cell = e.target.closest('.cal-annual-cell');
        if (!cell) return null;
        return {
            semana: parseInt(cell.dataset.semana, 10),
            sedeId: parseInt(cell.dataset.sedeId || '0', 10),
        };
    }

    function initAnnual() {
        const root = getRoot();
        if (!root) return;

        /* Click / drag on empty cells to create */
        root.addEventListener('mousedown', e => {
            if (getVista() !== 'anual') return;
            if (!isPlanMode() || annResizing || annMoving) return;
            if (e.target.closest('[data-bar-anual]')) return;
            const cell = e.target.closest('.cal-annual-cell');
            if (!cell) return;
            e.preventDefault();
            annualDragging  = true;
            annDragSedeId   = parseInt(cell.dataset.sedeId || '0', 10);
            annualDragStart = annualDragEnd = parseInt(cell.dataset.semana, 10);
        });

        root.addEventListener('mouseover', e => {
            if (!annualDragging) return;
            const cell = e.target.closest('.cal-annual-cell');
            if (cell && parseInt(cell.dataset.sedeId || '0', 10) === annDragSedeId) {
                annualDragEnd = parseInt(cell.dataset.semana, 10);
                annUpdateHighlight();
            }
        });

        /* Bar interactions */
        function attachAnnualBars() {
            root.querySelectorAll('[data-bar-anual]').forEach(chip => {
                if (chip._annInit) return;
                chip._annInit = true;
                const id = parseInt(chip.dataset.barAnual, 10);

                chip.addEventListener('click', e => {
                    e.stopPropagation();
                    if (!annWasDragging) getWire()?.$call('editarPlanificacion', id);
                });

                /* Resize handles */
                const hl = chip.querySelector('[data-ann-resize="left"]');
                const hr = chip.querySelector('[data-ann-resize="right"]');

                if (hl) {
                    hl.addEventListener('click', e => e.stopPropagation());
                    hl.addEventListener('mousedown', e => {
                        if (!isPlanMode()) return;
                        e.stopPropagation(); e.preventDefault();
                        annResizing = true; annResizeId = id; annResizeEdge = 'inicio';
                        annResizeSemana = annResizeOrig = parseInt(hl.dataset.semana, 10);
                        chip.classList.add('bar-dragging');
                    });
                }

                if (hr) {
                    hr.addEventListener('click', e => e.stopPropagation());
                    hr.addEventListener('mousedown', e => {
                        if (!isPlanMode()) return;
                        e.stopPropagation(); e.preventDefault();
                        annResizing = true; annResizeId = id; annResizeEdge = 'fin';
                        annResizeSemana = annResizeOrig = parseInt(hr.dataset.semana, 10);
                        chip.classList.add('bar-dragging');
                    });
                }

                /* Move handle */
                const mh = chip.querySelector('[data-ann-move]');
                if (mh) {
                    mh.addEventListener('mousedown', e => {
                        if (!isPlanMode()) return;
                        e.stopPropagation(); e.preventDefault();
                        annMoving = true; annMoveId = id;
                        annMoveDuration = parseInt(mh.dataset.duration, 10);
                        annMoveSemana = annMoveOrig = parseInt(mh.dataset.semana, 10);
                        /* Capture current sede from the parent cell */
                        const parentCell = chip.closest('.cal-annual-cell');
                        annMoveTargetSedeId = annMoveOrigSedeId = parentCell
                            ? parseInt(parentCell.dataset.sedeId || '0', 10)
                            : 0;
                        chip.classList.add('bar-dragging');
                    });
                }

                /* Delete button — show confirm dialog, stop propagation to chip click */
                chip.querySelectorAll('[data-ann-delete]').forEach(del => {
                    del.addEventListener('click', e => {
                        e.stopPropagation();
                        showConfirmDelete(parseInt(del.dataset.annDelete, 10));
                    });
                });
            });
        }

        attachAnnualBars();
        document.addEventListener('livewire:updated', attachAnnualBars);
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  VISTA MENSUAL — interactions (sin cambios)                         */
    /* ════════════════════════════════════════════════════════════════════ */

    let dragging = false, dragStart = null, dragEnd = null;
    let wasDragging = false;
    let resizing = false, resizePlanId = null, resizeEdge = null,
        resizeDay = null, resizeSurface = null, origResizeDay = null;
    let moving = false, movePlanId = null, movePlanSpan = 0,
        moveCurrentDay = null, moveSurface = null, origMoveDay = null;

    function daysInMonth() {
        const r = getRoot();
        return r ? parseInt(r.dataset.daysInMonth, 10) : 30;
    }

    function updateHighlight() {
        const lo = Math.min(dragStart, dragEnd), hi = Math.max(dragStart, dragEnd);
        const root = getRoot();
        if (!root) return;
        root.querySelectorAll('[data-day]').forEach(el => {
            const d = parseInt(el.dataset.day, 10);
            el.classList.toggle('cal-day-drag-highlight', d >= lo && d <= hi);
        });
    }
    function clearHighlight() {
        const root = getRoot();
        if (!root) return;
        root.querySelectorAll('[data-day]').forEach(el =>
            el.classList.remove('cal-day-drag-highlight'));
    }

    function resolveDay(e) {
        const surface = resizeSurface || moveSurface;
        if (!surface) return null;
        const rect = surface.getBoundingClientRect();
        const col  = Math.max(1, Math.min(7,
            Math.floor((e.clientX - rect.left) / (rect.width / 7)) + 1));
        const cell = surface.querySelector(`[data-col="${col}"][data-day]`);
        if (cell) return parseInt(cell.dataset.day, 10) || null;
        if (col === 1) return 1;
        if (col === 7) return daysInMonth();
        return resizeDay ?? moveCurrentDay;
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  GLOBAL mousedown — vista mensual (creates / drags)                 */
    /* ════════════════════════════════════════════════════════════════════ */

    document.addEventListener('mousemove', e => {
        /* Annual resize/move */
        if (annResizing) {
            const target = resolveAnnCell(e);
            if (target !== null) {
                annResizeSemana = target.semana;
                showTip((annResizeEdge === 'inicio' ? 'Inicio: S' : 'Fin: S') + target.semana, e.clientX, e.clientY);
            }
            return;
        }
        if (annMoving) {
            const target = resolveAnnCell(e);
            if (target !== null) {
                annMoveSemana      = target.semana;
                annMoveTargetSedeId = target.sedeId;
                const end = annMoveSemana + annMoveDuration;
                showTip(`Mover: S${annMoveSemana}→S${end}`, e.clientX, e.clientY);
                annMoveHighlight(annMoveSemana, annMoveDuration, annMoveTargetSedeId);
            }
            return;
        }

        /* Monthly resize/move */
        if (dragging) {
            const root = getRoot();
            if (!root) return;
            const cell = e.target.closest('[data-day]');
            if (cell && cell.dataset.day) {
                dragEnd = parseInt(cell.dataset.day, 10);
                updateHighlight();
            }
            return;
        }
        if (resizing) {
            const d = resolveDay(e);
            if (d !== null) {
                resizeDay = d;
                showTip((resizeEdge === 'inicio' ? 'Inicio: ' : 'Fin: ') +
                        String(d).padStart(2, '0'), e.clientX, e.clientY);
            }
            return;
        }
        if (moving) {
            const d = resolveDay(e);
            if (d !== null) {
                moveCurrentDay = d;
                const end = Math.min(d + movePlanSpan, daysInMonth());
                showTip(`Mover: día ${d} → ${end}`, e.clientX, e.clientY);
            }
        }
    });

    document.addEventListener('mouseup', () => {
        hideTip();
        annClearHighlight();

        /* Annual resize */
        if (annResizing) {
            if (annResizeId !== null && annResizeSemana !== annResizeOrig) {
                annWasDragging = true;
                setTimeout(() => { annWasDragging = false; }, 0);
                getWire()?.$call('ajustarBordePlanificacionSemana', annResizeId, annResizeEdge, annResizeSemana);
            }
            document.querySelector(`[data-bar-anual="${annResizeId}"]`)?.classList.remove('bar-dragging');
            annResizing = false; annResizeId = null; annResizeEdge = null;
            annResizeSemana = null; annResizeOrig = null;
            return;
        }

        /* Annual move */
        if (annMoving) {
            const id       = annMoveId;
            const destSema = annMoveSemana;
            const destSede = annMoveTargetSedeId;
            const didMove  = id !== null &&
                (annMoveSemana !== annMoveOrig || annMoveTargetSedeId !== annMoveOrigSedeId);

            document.querySelector(`[data-bar-anual="${id}"]`)?.classList.remove('bar-dragging');
            annMoving = false; annMoveId = null; annMoveDuration = 0;
            annMoveSemana = null; annMoveOrig = null;
            annMoveTargetSedeId = 0; annMoveOrigSedeId = 0;

            /* Suppress the chip's click listener to avoid double-firing */
            annWasDragging = true;
            setTimeout(() => { annWasDragging = false; }, 0);

            if (didMove) {
                getWire()?.$call('moverPlanificacionSemanas', id, destSema, destSede);
            } else {
                /* Plain click on chip — open edit modal */
                getWire()?.$call('editarPlanificacion', id);
            }
            return;
        }

        /* Annual drag-to-create */
        if (annualDragging) {
            annualDragging = false;
            annClearHighlight();
            const lo = Math.min(annualDragStart, annualDragEnd);
            const hi = Math.max(annualDragStart, annualDragEnd);
            if (lo === hi) getWire()?.$call('abrirModalAnualSemana', lo, annDragSedeId);
            else           getWire()?.$call('abrirModalAnualRango', lo, hi, annDragSedeId);
            annualDragStart = null; annualDragEnd = null; annDragSedeId = 0;
            return;
        }

        /* Monthly resize */
        if (resizing) {
            if (resizePlanId !== null && resizeDay !== origResizeDay) {
                wasDragging = true;
                setTimeout(() => { wasDragging = false; }, 0);
                getWire()?.$call('ajustarBordePlanificacion', resizePlanId, resizeEdge, resizeDay);
            }
            getRoot()?.querySelector(`[data-bar-id="${resizePlanId}"]`)?.classList.remove('bar-dragging');
            resizing = false; resizePlanId = null; resizeEdge = null;
            resizeDay = null; resizeSurface = null; origResizeDay = null;
            return;
        }

        /* Monthly move */
        if (moving) {
            if (movePlanId !== null && moveCurrentDay !== origMoveDay) {
                wasDragging = true;
                setTimeout(() => { wasDragging = false; }, 0);
                getWire()?.$call('moverPlanificacion', movePlanId, moveCurrentDay);
            }
            getRoot()?.querySelector(`[data-bar-id="${movePlanId}"]`)?.classList.remove('bar-dragging');
            moving = false; movePlanId = null; movePlanSpan = 0;
            moveCurrentDay = null; moveSurface = null; origMoveDay = null;
            return;
        }

        /* Monthly drag-to-create */
        if (dragging) {
            dragging = false;
            clearHighlight();
            const lo = Math.min(dragStart, dragEnd), hi = Math.max(dragStart, dragEnd);
            if (lo === hi) getWire()?.$call('abrirModalPlanificacion', lo);
            else           getWire()?.$call('abrirModalPlanificacionRango', lo, hi);
            dragStart = null; dragEnd = null;
        }
    });

    /* ════════════════════════════════════════════════════════════════════ */
    /*  GLOBAL mousedown — vista mensual (starts create/resize/move)       */
    /* ════════════════════════════════════════════════════════════════════ */

    function initMonthly() {
        const root = getRoot();
        if (!root) return;

        root.addEventListener('mousedown', e => {
            if (getVista() !== 'mensual') return;
            if (!isPlanMode() || resizing || moving) return;
            if (e.target.closest('[data-bar-id]')) return;
            const cell = e.target.closest('[data-day]');
            if (!cell) return;
            e.preventDefault();
            dragging = true;
            dragStart = dragEnd = parseInt(cell.dataset.day, 10);
            updateHighlight();
        });

        root.addEventListener('mouseover', e => {
            if (!dragging) return;
            const cell = e.target.closest('[data-day]');
            if (cell && cell.dataset.day) {
                dragEnd = parseInt(cell.dataset.day, 10);
                updateHighlight();
            }
        });
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  Monthly bars: resize / move handles                                */
    /* ════════════════════════════════════════════════════════════════════ */

    function attachMonthlyBars() {
        const root = getRoot();
        if (!root || getVista() !== 'mensual') return;

        root.querySelectorAll('[data-bar-id]').forEach(bar => {
            if (bar._calInit) return;
            bar._calInit = true;

            const id = parseInt(bar.dataset.barId, 10);

            bar.addEventListener('click', e => {
                e.stopPropagation();
                if (!wasDragging) getWire()?.$call('editarPlanificacion', id);
            });

            const hl = bar.querySelector('[data-resize="left"]');
            if (hl) {
                hl.addEventListener('click', e => e.stopPropagation());
                hl.addEventListener('mousedown', e => {
                    if (!isPlanMode()) return;
                    e.stopPropagation(); e.preventDefault();
                    resizing = true; resizePlanId = id; resizeEdge = 'inicio';
                    resizeDay = origResizeDay = parseInt(hl.dataset.day, 10);
                    resizeSurface = hl.closest('.cal-week');
                    bar.classList.add('bar-dragging');
                });
            }

            const hr = bar.querySelector('[data-resize="right"]');
            if (hr) {
                hr.addEventListener('click', e => e.stopPropagation());
                hr.addEventListener('mousedown', e => {
                    if (!isPlanMode()) return;
                    e.stopPropagation(); e.preventDefault();
                    resizing = true; resizePlanId = id; resizeEdge = 'fin';
                    resizeDay = origResizeDay = parseInt(hr.dataset.day, 10);
                    resizeSurface = hr.closest('.cal-week');
                    bar.classList.add('bar-dragging');
                });
            }

            const mh = bar.querySelector('[data-move]');
            if (mh) {
                mh.addEventListener('mousedown', e => {
                    if (!isPlanMode()) return;
                    e.stopPropagation(); e.preventDefault();
                    moving = true; movePlanId = id;
                    movePlanSpan = parseInt(mh.dataset.span, 10);
                    moveCurrentDay = origMoveDay = parseInt(mh.dataset.start, 10);
                    moveSurface = mh.closest('.cal-week');
                    bar.classList.add('bar-dragging');
                });
            }

            /* Delete button — confirm dialog */
            const delBtn = bar.querySelector('[data-bar-delete]');
            if (delBtn) {
                delBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    showConfirmDelete(parseInt(delBtn.dataset.barDelete, 10));
                });
            }
        });
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  KEYBOARD shortcuts                                                 */
    /* ════════════════════════════════════════════════════════════════════ */

    document.addEventListener('keydown', e => {
        if (e.target.matches('input, textarea, select')) return;
        if (e.key === 'Escape') {
            if (document.getElementById('planning-modal')) {
                getWire()?.$call('cerrarModal');
            } else if (isPlanMode()) {
                getWire()?.$call('toggleModoPlaneacion');
            }
            return;
        }
        if (getVista() === 'mensual') {
            if (e.key === 'ArrowLeft')  getWire()?.$call('mesAnterior');
            if (e.key === 'ArrowRight') getWire()?.$call('mesSiguiente');
        } else {
            if (e.key === 'ArrowLeft')  getWire()?.$call('irAnioAnterior');
            if (e.key === 'ArrowRight') getWire()?.$call('irAnioSiguiente');
        }
    });

    /* ════════════════════════════════════════════════════════════════════ */
    /*  INIT                                                               */
    /* ════════════════════════════════════════════════════════════════════ */

    function init() {
        initAnnual();
        initMonthly();
        attachMonthlyBars();
        initConfirmDelete();
    }

    document.addEventListener('livewire:initialized', init);
    document.addEventListener('livewire:updated', attachMonthlyBars);
})();
</script>
@endpush
