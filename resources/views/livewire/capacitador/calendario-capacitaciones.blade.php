{{--
    Tailwind safelist — dynamic course-palette classes (do not remove):
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div class="p-6 relative"
     x-data="{
        /* ── shared state ── */
        planMode: @entangle('modoPlaneacion'),

        /* ── create by drag (calendar view) ── */
        dragging: false,
        dragStartDay: null,
        dragEndDay: null,

        /* ── resize state ── */
        resizing: false,
        resizePlanId: null,
        resizeEdge: null,
        resizeDay: null,
        resizeKind: null,
        resizeSurface: null,

        /* ── move (drag whole bar) state ── */
        moving: false,
        movePlanId: null,
        movePlanSpan: 0,
        moveGrabDayOffset: 0,
        moveCurrentStartDay: null,
        moveKind: null,
        moveSurface: null,

        /* ── drag tracking (prevents click-after-drag from opening modal) ── */
        wasDragging: false,
        originalMoveDay: null,
        originalResizeDay: null,

        /* ── tooltip ── */
        tooltipVisible: false,
        tooltipText: '',
        tooltipX: 0,
        tooltipY: 0,

        daysInMonth: {{ $diasEnMes }},

        /* ─────────────── helpers ─────────────── */

        inRange(day) {
            if (!this.dragging || !this.dragStartDay || !this.dragEndDay) return false;
            const lo = Math.min(this.dragStartDay, this.dragEndDay);
            const hi = Math.max(this.dragStartDay, this.dragEndDay);
            return day >= lo && day <= hi;
        },

        showTooltip(text, event) {
            this.tooltipText = text;
            this.tooltipX = event.clientX;
            this.tooltipY = event.clientY;
            this.tooltipVisible = true;
        },
        hideTooltip() {
            this.tooltipVisible = false;
        },

        /* ─────────────── create-drag ─────────────── */

        beginDrag(day) {
            if (!this.planMode || this.resizing || this.moving) return;
            this.dragging = true;
            this.dragStartDay = day;
            this.dragEndDay   = day;
        },
        trackDragDay(day) {
            if (!this.dragging || this.resizing || this.moving) return;
            this.dragEndDay = day;
        },

        /* ─────────────── resize ─────────────── */

        beginResize(planId, edge, day, kind, event) {
            if (!this.planMode) return;
            event.stopPropagation();
            this.resizing         = true;
            this.resizePlanId     = planId;
            this.resizeEdge       = edge;
            this.resizeDay        = day;
            this.originalResizeDay = day;
            this.resizeKind       = kind;
            this.resizeSurface    = event.currentTarget.closest(kind === 'gantt' ? '.gantt-container' : '.cal-week');
        },

        resolveDay(event) {
            if (!this.resizeSurface && !this.moveSurface) return null;
            const surface = this.resizeSurface || this.moveSurface;
            const kind = this.resizeKind || this.moveKind;

            if (kind === 'gantt') {
                const rect = surface.getBoundingClientRect();
                const labelWidth = 200;
                const usable = Math.max(rect.width - labelWidth, 1);
                const rx = Math.max(0, Math.min(event.clientX - rect.left - labelWidth, usable - 1));
                return Math.max(1, Math.min(this.daysInMonth, Math.floor(rx / (usable / this.daysInMonth)) + 1));
            }

            /* calendar week */
            const rect = surface.getBoundingClientRect();
            const col = Math.max(1, Math.min(7, Math.floor((event.clientX - rect.left) / (rect.width / 7)) + 1));
            const cell = surface.querySelector('[data-col=' + col + '][data-day]');
            if (cell) return Number.parseInt(cell.dataset.day, 10) || null;
            if (col === 1) return 1;
            if (col === 7) return this.daysInMonth;
            return this.resizeDay || this.moveCurrentStartDay;
        },

        /* ─────────────── move ─────────────── */

        beginMove(planId, planSpan, grabDay, kind, event) {
            if (!this.planMode) return;
            this.moving              = true;
            this.movePlanId          = planId;
            this.movePlanSpan        = planSpan;
            this.moveGrabDayOffset   = 0;
            this.moveCurrentStartDay = grabDay;
            this.originalMoveDay     = grabDay;
            this.moveKind            = kind;
            this.moveSurface         = event.currentTarget.closest(kind === 'gantt' ? '.gantt-container' : '.cal-week');
        },

        /* ─────────────── global pointer tracking ─────────────── */

        trackPointer(event) {
            if (!this.dragging && !this.resizing && !this.moving) return;

            if (this.resizing) {
                const day = this.resolveDay(event);
                if (day) {
                    this.resizeDay = day;
                    const label = this.resizeEdge === 'inicio' ? 'Inicio: ' : 'Fin: ';
                    this.showTooltip(label + this.dayLabel(day), event);
                }
                return;
            }

            if (this.moving) {
                const day = this.resolveDay(event);
                if (day) {
                    this.moveCurrentStartDay = day;
                    const endDay = Math.min(day + this.movePlanSpan, this.daysInMonth);
                    this.showTooltip('Mover: d\u00eda ' + day + ' \u2192 ' + endDay, event);
                }
                return;
            }

            /* dragging: try to find day from element or global mouse pos */
            const cell = event.target.closest('[data-day]');
            if (cell) {
                const day = Number.parseInt(cell.dataset.day, 10);
                if (!Number.isNaN(day) && day >= 1) this.dragEndDay = day;
            }
        },

        dayLabel(day) {
            return String(day).padStart(2, '0');
        },

        /* ─────────────── finish ─────────────── */

        finishInteraction() {
            this.hideTooltip();

            if (this.resizing) {
                if (this.resizePlanId && this.resizeEdge && this.resizeDay !== null
                        && this.resizeDay !== this.originalResizeDay) {
                    this.wasDragging = true;
                    setTimeout(() => { this.wasDragging = false; }, 0);
                    $wire.ajustarBordePlanificacion(this.resizePlanId, this.resizeEdge, this.resizeDay);
                }
                this.resizing = false; this.resizePlanId = null; this.resizeEdge = null;
                this.resizeDay = null; this.resizeKind = null; this.resizeSurface = null;
                this.originalResizeDay = null;
                return;
            }

            if (this.moving) {
                if (this.movePlanId && this.moveCurrentStartDay !== null
                        && this.moveCurrentStartDay !== this.originalMoveDay) {
                    this.wasDragging = true;
                    setTimeout(() => { this.wasDragging = false; }, 0);
                    $wire.moverPlanificacion(this.movePlanId, this.moveCurrentStartDay);
                }
                this.moving = false; this.movePlanId = null; this.movePlanSpan = 0;
                this.moveGrabDayOffset = 0; this.moveCurrentStartDay = null;
                this.moveKind = null; this.moveSurface = null;
                this.originalMoveDay = null;
                return;
            }

            if (this.dragging) {
                this.dragging = false;
                const lo = Math.min(this.dragStartDay, this.dragEndDay);
                const hi = Math.max(this.dragStartDay, this.dragEndDay);
                if (lo === hi) {
                    $wire.abrirModalPlanificacion(lo);
                } else {
                    $wire.abrirModalPlanificacionRango(lo, hi);
                }
                this.dragStartDay = null; this.dragEndDay = null;
            }
        }
     }"
     :class="{ 'plan-mode-active': planMode }"
     @mousemove.window="trackPointer($event)"
     @mouseup.window="finishInteraction()"
     @keydown.escape.window="if (planMode) $wire.toggleModoPlaneacion()"
     @keydown.arrow-left.window="if (!$event.target.matches('input,textarea,select')) $wire.mesAnterior()"
     @keydown.arrow-right.window="if (!$event.target.matches('input,textarea,select')) $wire.mesSiguiente()">

    {{-- Floating tooltip during drag / resize / move --}}
    <div x-show="tooltipVisible"
         x-cloak
         class="cal-drag-tooltip"
         :style="'left:' + tooltipX + 'px; top:' + tooltipY + 'px'"
         x-text="tooltipText"></div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-5 bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4 flex-wrap">

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
            <button wire:click="mesAnterior"
                    wire:loading.attr="disabled"
                    wire:target="mesAnterior,mesSiguiente,irAHoy"
                    title="Mes anterior (&#x2190;)"
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
                    title="Mes siguiente (&#x2192;)"
                    class="w-9 h-9 flex items-center justify-center bg-gray-100 text-gray-600 rounded-lg font-bold nav-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <h2 class="text-xl font-bold text-gray-800 ml-1 min-w-32"
                wire:loading.class="opacity-50"
                wire:target="mesAnterior,mesSiguiente,irAHoy">
                {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }}
                {{ $anioActual }}
            </h2>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Planning mode toggle (admin only) --}}
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
                        Salir de planificaci&oacute;n
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
                <strong>Modo planificaci&oacute;n activo.</strong>
                Arrastra un rango en el calendario para crear, haz clic en una barra para editar, arrastra su centro para mover el periodo, o usa los bordes para ajustar inicio y fin.
                Presiona <kbd class="px-1 py-0.5 bg-blue-100 rounded text-xs font-mono">Esc</kbd> para salir.
            </span>
        </div>
    @endif

    {{-- ── Main layout: calendar + optional sidebar ──────────────────── --}}
    <div class="flex gap-4 items-start">

        {{-- ── CALENDAR VIEW ─────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">

            {{-- Day-of-week header --}}
            <div class="cal-header">
                @foreach(['Lun','Mar','Mi&eacute;','Jue','Vie','S&aacute;b','Dom'] as $dowIdx => $dow)
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
                        <div class="cal-day-cell {{ $diaInfo['esHoy'] ? 'cal-day-today' : '' }}
                                    {{ !$diaInfo['esMesActual'] ? 'cal-day-outside' : '' }}
                                    {{ $diaInfo['esWeekend'] && $diaInfo['esMesActual'] ? 'cal-day-weekend' : '' }}"
                             style="grid-column:{{ $dIdx + 1 }};grid-row:1"
                             data-col="{{ $dIdx + 1 }}"
                             @if($diaInfo['esMesActual']) data-day="{{ $diaInfo['num'] }}" @endif
                             :class="{ 'cal-day-drag-highlight': planMode && inRange({{ $diaInfo['esMesActual'] ? $diaInfo['num'] : 0 }}) }"
                             @if($diaInfo['esMesActual'])
                                @mousedown.prevent="beginDrag({{ $diaInfo['num'] }})"
                                @mouseenter="trackDragDay({{ $diaInfo['num'] }})"
                             @endif>
                            <span class="{{ $diaInfo['esHoy']
                                ? 'text-white bg-Alumco-blue rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold'
                                : 'text-sm font-bold text-gray-500' }}">
                                {{ $diaInfo['num'] }}
                            </span>
                        </div>
                    @endforeach

                    {{-- Course bars --}}
                    @foreach($semana['barras'] as $barra)
                        @php $esPrimerSegmento = $barra['roundLeft'] || $barra['extiendePorIzq']; @endphp
                        <div wire:key="bar-cal-{{ $semIdx }}-{{ $barra['id'] }}"
                             class="cal-bar {{ $barra['bg'] }}
                                    {{ $barra['roundLeft']  ? 'rounded-l-md' : '' }}
                                    {{ $barra['roundRight'] ? 'rounded-r-md' : '' }}
                                    {{ !$esPrimerSegmento ? 'cal-bar-continuation' : '' }}"
                             style="grid-column: {{ $barra['col'] }} / span {{ $barra['span'] }}; grid-row: {{ $barra['slot'] + 2 }}"
                             title="{{ $barra['titulo'] }} ({{ $barra['fechaIni'] }} &rarr; {{ $barra['fechaFin'] }}){{ $barra['notas'] ? ' \u2014 '.$barra['notas'] : '' }}"
                             :class="{ 'bar-dragging': (moving && movePlanId === {{ $barra['id'] }}) || (resizing && resizePlanId === {{ $barra['id'] }}) }"
                             @if($esAdmin)
                                @click.stop="if (!wasDragging) $wire.editarPlanificacion({{ $barra['id'] }})"
                             @endif>

                            {{-- Left resize handle (planning mode) --}}
                            @if($esAdmin && $modoPlaneacion)
                                <button type="button"
                                        class="bar-resize-handle bar-resize-left"
                                        title="Mover inicio"
                                        @click.stop
                                        @mousedown.stop.prevent="beginResize({{ $barra['id'] }}, 'inicio', {{ $barra['edgeStartDay'] }}, 'calendar', $event)"></button>
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
                                     @mousedown.prevent.stop="beginMove({{ $barra['id'] }}, {{ $barra['span'] }}, {{ $barra['segStartDay'] }}, 'calendar', $event)">
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

                            {{-- Right resize handle (planning mode) --}}
                            @if($esAdmin && $modoPlaneacion)
                                <button type="button"
                                        class="bar-resize-handle bar-resize-right"
                                        title="Mover fin"
                                        @click.stop
                                        @mousedown.stop.prevent="beginResize({{ $barra['id'] }}, 'fin', {{ $barra['edgeEndDay'] }}, 'calendar', $event)"></button>
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

        {{-- ── Sin Planificar Sidebar (admin + planning mode) ────────── --}}
        @if($esAdmin && $modoPlaneacion)
            @php
                $sinPlan    = count($cursosSinPlanificar);
                $planificados = count($cursosDisponibles) - $sinPlan;
            @endphp
            <div class="plan-sidebar" x-data="{ search: '' }">

                {{-- Header --}}
                <div class="plan-sidebar-header">
                    <span>Sin planificar</span>
                    <span class="plan-sidebar-badge">{{ $sinPlan }}</span>
                </div>

                @if($sinPlan > 0)
                    {{-- Search --}}
                    <div class="plan-sidebar-search">
                        <input type="text"
                               x-model="search"
                               placeholder="Buscar curso..."
                               autocomplete="off">
                    </div>

                    {{-- List --}}
                    <div class="plan-sidebar-list">
                        @foreach($cursosSinPlanificar as $curso)
                            <div class="plan-sidebar-item"
                                 x-show="search === '' || '{{ strtolower(addslashes($curso['titulo'])) }}'.includes(search.toLowerCase())"
                                 wire:click="abrirModalConCurso({{ $curso['id'] }})"
                                 title="Planificar: {{ $curso['titulo'] }}">
                                <span class="plan-sidebar-item-dot {{ $curso['bg'] }}"></span>
                                <span class="plan-sidebar-item-title">{{ $curso['titulo'] }}</span>
                                <button type="button" class="plan-sidebar-item-btn" tabindex="-1"
                                        title="Añadir al calendario">+</button>
                            </div>
                        @endforeach
                        <template x-if="false"><!-- alpine anchor --></template>
                    </div>
                @else
                    {{-- Empty state: everything is planned --}}
                    <div class="plan-sidebar-empty">
                        <svg class="w-8 h-8 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-semibold text-gray-700 text-xs text-center leading-snug">
                            ¡Todos los cursos están planificados este mes!
                        </p>
                    </div>
                @endif

                {{-- Footer: planned count --}}
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
        <div class="fixed inset-0 bg-black/50 z-50 flex justify-center items-center backdrop-blur-sm"
             x-data="{
                query: '',
                selectedId: {{ $cursoId ?? 'null' }},
                get filteredCursos() {
                    if (!this.query) return @js($cursosDisponibles);
                    return (@js($cursosDisponibles)).filter(c =>
                        c.titulo.toLowerCase().includes(this.query.toLowerCase())
                    );
                },
                select(id) {
                    this.selectedId = id;
                    $wire.set('cursoId', id);
                }
             }"
             @keydown.escape.stop="$wire.cerrarModal()">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden mx-4"
                 @click.stop>

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

                        {{-- Hidden wire binding --}}
                        <input type="hidden" wire:model="cursoId">

                        <div class="modal-course-search">
                            <svg class="modal-course-search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            <input type="text"
                                   x-model="query"
                                   placeholder="Buscar curso..."
                                   autocomplete="off">
                        </div>

                        <div class="modal-course-list">
                            <template x-for="curso in filteredCursos" :key="curso.id">
                                <div class="modal-course-option"
                                     :class="{ 'selected': selectedId === curso.id }"
                                     @click="select(curso.id)">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 bg-blue-500"></span>
                                    <span x-text="curso.titulo"></span>
                                    <svg x-show="selectedId === curso.id" class="w-4 h-4 ml-auto text-Alumco-blue shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="filteredCursos.length === 0">
                                <p class="text-xs text-gray-400 text-center py-3">Sin resultados</p>
                            </template>
                        </div>

                        @error('cursoId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Date range --}}
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
