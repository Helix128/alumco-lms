{{--
    Tailwind safelist — dynamic course-palette classes (do not remove):
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div id="cal-root"
     data-plan-mode="{{ $modoPlaneacion ? '1' : '0' }}"
     data-vista="{{ $modoVista }}"
     data-days-in-month="{{ $diasEnMes }}"
     @class(['p-6 relative', 'plan-mode-active' => $modoPlaneacion])>

    {{-- Floating tooltip during drag / resize / move (shown/hidden by JS) --}}
    <div id="cal-tooltip" class="cal-drag-tooltip" aria-hidden="true"></div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-5 bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4 flex-wrap">

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

        {{-- Right controls: view toggle + plan mode --}}
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

            @if($esAdmin)
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
        <div class="mb-4 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                <strong>Modo planificación activo.</strong>
                @if($modoVista === 'anual')
                    Haz clic en una semana vac&iacute;a para asignar un curso; arrastra para seleccionar varias semanas.
                    Haz clic en un bloque para editar o eliminarlo.
                @else
                    Arrastra un rango en el calendario para crear, haz clic en una barra para editar, arrastra su centro para mover el periodo, o usa los bordes para ajustar inicio y fin.
                @endif
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

                            {{-- ── Fila 4: Cursos ────────────────────── --}}
                            <div class="cal-annual-row-label">
                                <svg class="w-4 h-4 text-gray-400 mr-1.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Capacitaciones
                            </div>
                            @foreach($semanasDelAnio as $sem)
                                @php
                                    $tieneCursos = count($sem['cursos']) > 0;
                                @endphp
                                <div @class([
                                        'cal-annual-cell',
                                        'cal-annual-cell-empty'    => ! $tieneCursos,
                                        'cal-annual-cell-conflict' => $sem['conflicto'],
                                        'cal-annual-cell-past'     => $sem['esPasada'] && ! $sem['esHoy'],
                                        'cal-annual-cell-today'    => $sem['esHoy'],
                                     ])
                                     data-semana="{{ $sem['numero'] }}"
                                     data-fecha-inicio="{{ $sem['inicio'] }}"
                                     data-fecha-fin="{{ $sem['fin'] }}">

                                    @foreach($sem['cursos'] as $curso)
                                        <div @class([
                                                'cal-annual-chip',
                                                $curso['bg'],
                                                'rounded-l-md'   => $curso['esInicio'],
                                                'rounded-r-md'   => $curso['esFin'],
                                             ])
                                             data-bar-anual="{{ $curso['id'] }}"
                                             wire:key="chip-{{ $sem['numero'] }}-{{ $curso['id'] }}"
                                             title="{{ $curso['titulo'] }}{{ $curso['notas'] ? ' — '.$curso['notas'] : '' }}">

                                            @if($curso['esInicio'])
                                                <span class="cal-annual-chip-label text-white text-[11px] font-semibold px-1.5 truncate leading-none">
                                                    {{ $curso['titulo'] }}
                                                </span>
                                            @endif

                                            @if($esAdmin && $modoPlaneacion && $curso['esFin'])
                                                <button type="button"
                                                        wire:click.stop="borrarPlanificacion({{ $curso['id'] }})"
                                                        class="cal-annual-chip-delete text-white/80 hover:text-white ml-auto shrink-0 px-1 leading-none"
                                                        title="Eliminar">
                                                    &times;
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($sem['conflicto'])
                                        <div class="cal-annual-conflict-badge" title="{{ count($sem['cursos']) }} cursos en esta semana">
                                            !
                                        </div>
                                    @endif
                                </div>
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
                        <div class="cal-week" style="grid-template-rows: auto repeat({{ max($semana['maxSlot'], 1) }}, 22px) 4px">

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
                                                <span class="cal-bar-label">{{ $barra['titulo'] }}</span>
                                            @endif
                                        </div>
                                    @elseif($esPrimerSegmento)
                                        <span class="cal-bar-label">{{ $barra['titulo'] }}</span>
                                    @endif

                                    {{-- Right cross-month arrow --}}
                                    @if($barra['extiendePorDer'])
                                        <span class="bar-arrow-right" title="Contin&uacute;a en el siguiente mes">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @endif

                                    {{-- Delete button --}}
                                    @if($esAdmin)
                                        <button wire:click.stop="borrarPlanificacion({{ $barra['id'] }})"
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

                            {{-- Empty-state hint in planning mode --}}
                            @if($modoPlaneacion && count($semana['barras']) === 0)
                                <div class="pointer-events-none text-[10px] text-gray-300 italic px-2 py-1"
                                     style="grid-column: 1 / -1; grid-row: 2">
                                    Arrastra para planificar
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>{{-- /.flex-1 --}}

        {{-- ── Sin Planificar Sidebar (admin + planning mode) ────────── --}}
        @if($esAdmin && $modoPlaneacion)
            @php
                $sinPlan      = count($cursosSinPlanificar);
                $planificados = count($cursosDisponibles) - $sinPlan;
            @endphp
            <div class="plan-sidebar">

                {{-- Header --}}
                <div class="plan-sidebar-header">
                    <span>Sin planificar</span>
                    <span class="plan-sidebar-badge">{{ $sinPlan }}</span>
                </div>

                @if($sinPlan > 0)
                    {{-- Search --}}
                    <div class="plan-sidebar-search">
                        <input type="text"
                               wire:model.live.debounce.300ms="busquedaSidebar"
                               placeholder="Buscar curso..."
                               autocomplete="off">
                    </div>

                    {{-- List --}}
                    <div class="plan-sidebar-list">
                        @foreach($sidebarList as $curso)
                            <div class="plan-sidebar-item"
                                 wire:click="abrirModalConCurso({{ $curso['id'] }})"
                                 wire:key="sb-{{ $curso['id'] }}"
                                 title="Planificar: {{ $curso['titulo'] }}">
                                <span class="plan-sidebar-item-dot {{ $curso['bg'] }}"></span>
                                <span class="plan-sidebar-item-title">{{ $curso['titulo'] }}</span>
                                <button type="button" class="plan-sidebar-item-btn" tabindex="-1">+</button>
                            </div>
                        @endforeach
                        @if(! count($sidebarList) && $busquedaSidebar)
                            <p class="text-xs text-gray-400 text-center py-3">Sin resultados</p>
                        @endif
                    </div>
                @else
                    {{-- Empty state --}}
                    <div class="plan-sidebar-empty">
                        <svg class="w-8 h-8 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-semibold text-gray-700 text-xs text-center leading-snug">
                            &iexcl;Todos los cursos están planificados este {{ $modoVista === 'anual' ? 'año' : 'mes' }}!
                        </p>
                    </div>
                @endif

                {{-- Footer --}}
                @if($planificados > 0)
                    <div class="plan-sidebar-footer">
                        <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                  clip-rule="evenodd"/>
                        </svg>
                        {{ $planificados }} de {{ count($cursosDisponibles) }} planificado{{ $planificados !== 1 ? 's' : '' }}
                    </div>
                @endif

            </div>
        @endif

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
    /*  VISTA ANUAL — interactions                                         */
    /* ════════════════════════════════════════════════════════════════════ */

    let annualDragStart = null, annualDragEnd = null, annualDragging = false;
    /* resizing / moving in annual view */
    let annResizing = false, annResizeId = null, annResizeEdge = null, annResizeSemana = null, annResizeOrig = null;
    let annMoving = false, annMoveId = null, annMoveDuration = 0, annMoveSemana = null, annMoveOrig = null;
    let annWasDragging = false;

    function annClearHighlight() {
        document.querySelectorAll('.cal-annual-cell-drag-highlight').forEach(el =>
            el.classList.remove('cal-annual-cell-drag-highlight'));
    }

    function annUpdateHighlight() {
        if (annualDragStart === null || annualDragEnd === null) return;
        const lo = Math.min(annualDragStart, annualDragEnd);
        const hi = Math.max(annualDragStart, annualDragEnd);
        document.querySelectorAll('[data-semana]').forEach(el => {
            const s = parseInt(el.dataset.semana, 10);
            el.classList.toggle('cal-annual-cell-drag-highlight', s >= lo && s <= hi);
        });
    }

    function initAnnual() {
        const root = getRoot();
        if (!root) return;

        /* Click / drag on empty cells to create */
        root.addEventListener('mousedown', e => {
            if (getVista() !== 'anual') return;
            if (!isPlanMode() || annResizing || annMoving) return;
            if (e.target.closest('[data-bar-anual]')) return;
            const cell = e.target.closest('[data-semana]');
            if (!cell) return;
            e.preventDefault();
            annualDragging  = true;
            annualDragStart = annualDragEnd = parseInt(cell.dataset.semana, 10);
        });

        root.addEventListener('mouseover', e => {
            if (!annualDragging) return;
            const cell = e.target.closest('[data-semana]');
            if (cell) {
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
                        chip.classList.add('bar-dragging');
                    });
                }
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

    /* Semana cell hover for annual resize/move */
    function resolveAnnSemana(e) {
        const cell = e.target.closest('[data-semana]');
        return cell ? parseInt(cell.dataset.semana, 10) : null;
    }

    /* ════════════════════════════════════════════════════════════════════ */
    /*  GLOBAL mousedown — vista mensual (creates / drags)                 */
    /* ════════════════════════════════════════════════════════════════════ */

    document.addEventListener('mousemove', e => {
        /* Annual resize/move */
        if (annResizing) {
            const s = resolveAnnSemana(e);
            if (s !== null) {
                annResizeSemana = s;
                showTip((annResizeEdge === 'inicio' ? 'Inicio: S' : 'Fin: S') + s, e.clientX, e.clientY);
            }
            return;
        }
        if (annMoving) {
            const s = resolveAnnSemana(e);
            if (s !== null) {
                annMoveSemana = s;
                const end = annMoveSemana + annMoveDuration;
                showTip(`Mover: S${annMoveSemana} → S${end}`, e.clientX, e.clientY);
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
            if (annMoveId !== null && annMoveSemana !== annMoveOrig) {
                annWasDragging = true;
                setTimeout(() => { annWasDragging = false; }, 0);
                getWire()?.$call('moverPlanificacionSemanas', annMoveId, annMoveSemana);
            }
            document.querySelector(`[data-bar-anual="${annMoveId}"]`)?.classList.remove('bar-dragging');
            annMoving = false; annMoveId = null; annMoveDuration = 0;
            annMoveSemana = null; annMoveOrig = null;
            return;
        }

        /* Annual drag-to-create */
        if (annualDragging) {
            annualDragging = false;
            annClearHighlight();
            const lo = Math.min(annualDragStart, annualDragEnd);
            const hi = Math.max(annualDragStart, annualDragEnd);
            if (lo === hi) getWire()?.$call('abrirModalAnualSemana', lo);
            else           getWire()?.$call('abrirModalAnualRango', lo, hi);
            annualDragStart = null; annualDragEnd = null;
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
    }

    document.addEventListener('livewire:initialized', init);
    document.addEventListener('livewire:updated', attachMonthlyBars);
})();
</script>
@endpush
