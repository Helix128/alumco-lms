{{--
    Tailwind safelist — dynamic course-palette classes (do not remove):
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div id="cal-root"
     data-plan-mode="{{ $modoPlaneacion ? '1' : '0' }}"
     data-days-in-month="{{ $diasEnMes }}"
     @class(['p-6 relative', 'plan-mode-active' => $modoPlaneacion])>

    {{-- Floating tooltip during drag / resize / move (shown/hidden by JS) --}}
    <div id="cal-tooltip" class="cal-drag-tooltip" aria-hidden="true"></div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-5 bg-white p-4 rounded-xl shadow-sm border border-gray-200 gap-4 flex-wrap">

        {{-- Navigation --}}
        <div class="flex items-center gap-2">
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

            <h2 class="text-xl font-bold text-gray-800 ml-1 min-w-32"
                wire:loading.class="opacity-50"
                wire:target="mesAnterior,mesSiguiente,irAHoy">
                {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }}
                {{ $anioActual }}
            </h2>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
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
                            ¡Todos los cursos están planificados este mes!
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

@push('scripts')
<script>
(function () {
    'use strict';

    function initCal() {
        const root = document.getElementById('cal-root');
        if (!root) return;

        const isPlanMode   = () => root.dataset.planMode === '1';
        const daysInMonth  = () => parseInt(root.dataset.daysInMonth, 10);
        const getWire      = () => window.Livewire.find(root.getAttribute('wire:id'));

        /* ── State ─────────────────────────────────────────────────────── */
        let dragging = false, dragStart = null, dragEnd = null;
        let wasDragging = false;
        let resizing = false, resizePlanId = null, resizeEdge = null,
            resizeDay = null, resizeSurface = null, origResizeDay = null;
        let moving = false, movePlanId = null, movePlanSpan = 0,
            moveCurrentDay = null, moveSurface = null, origMoveDay = null;

        /* ── Tooltip ────────────────────────────────────────────────────── */
        const tip = document.getElementById('cal-tooltip');
        function showTip(text, x, y) {
            if (!tip) return;
            tip.textContent = text;
            tip.style.cssText = `left:${x + 14}px;top:${y - 32}px;display:block`;
        }
        function hideTip() { if (tip) tip.style.display = 'none'; }

        /* ── Drag highlight on day cells ────────────────────────────────── */
        function updateHighlight() {
            const lo = Math.min(dragStart, dragEnd), hi = Math.max(dragStart, dragEnd);
            root.querySelectorAll('[data-day]').forEach(el => {
                const d = parseInt(el.dataset.day, 10);
                el.classList.toggle('cal-day-drag-highlight', d >= lo && d <= hi);
            });
        }
        function clearHighlight() {
            root.querySelectorAll('[data-day]').forEach(el =>
                el.classList.remove('cal-day-drag-highlight'));
        }

        /* ── Resolve day from mouse position (for resize / move) ────────── */
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

        /* ── Create-by-drag on day cells ────────────────────────────────── */
        root.addEventListener('mousedown', e => {
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

        /* ── Global mousemove ───────────────────────────────────────────── */
        document.addEventListener('mousemove', e => {
            if (dragging) {
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

        /* ── Global mouseup ─────────────────────────────────────────────── */
        document.addEventListener('mouseup', () => {
            hideTip();

            if (resizing) {
                if (resizePlanId !== null && resizeDay !== origResizeDay) {
                    wasDragging = true;
                    setTimeout(() => { wasDragging = false; }, 0);
                    getWire().$call('ajustarBordePlanificacion', resizePlanId, resizeEdge, resizeDay);
                }
                root.querySelector(`[data-bar-id="${resizePlanId}"]`)?.classList.remove('bar-dragging');
                resizing = false; resizePlanId = null; resizeEdge = null;
                resizeDay = null; resizeSurface = null; origResizeDay = null;
                return;
            }

            if (moving) {
                if (movePlanId !== null && moveCurrentDay !== origMoveDay) {
                    wasDragging = true;
                    setTimeout(() => { wasDragging = false; }, 0);
                    getWire().$call('moverPlanificacion', movePlanId, moveCurrentDay);
                }
                root.querySelector(`[data-bar-id="${movePlanId}"]`)?.classList.remove('bar-dragging');
                moving = false; movePlanId = null; movePlanSpan = 0;
                moveCurrentDay = null; moveSurface = null; origMoveDay = null;
                return;
            }

            if (dragging) {
                dragging = false;
                clearHighlight();
                const lo = Math.min(dragStart, dragEnd), hi = Math.max(dragStart, dragEnd);
                if (lo === hi) getWire().$call('abrirModalPlanificacion', lo);
                else           getWire().$call('abrirModalPlanificacionRango', lo, hi);
                dragStart = null; dragEnd = null;
            }
        });

        /* ── Keyboard shortcuts ─────────────────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (e.target.matches('input, textarea, select')) return;
            if (e.key === 'Escape') {
                if (document.getElementById('planning-modal')) {
                    getWire().$call('cerrarModal');
                } else if (isPlanMode()) {
                    getWire().$call('toggleModoPlaneacion');
                }
                return;
            }
            if (e.key === 'ArrowLeft')  getWire().$call('mesAnterior');
            if (e.key === 'ArrowRight') getWire().$call('mesSiguiente');
        });

        /* ── Attach listeners to bar elements ───────────────────────────── */
        function attachBars() {
            root.querySelectorAll('[data-bar-id]').forEach(bar => {
                if (bar._calInit) return;
                bar._calInit = true;

                const id = parseInt(bar.dataset.barId, 10);

                bar.addEventListener('click', e => {
                    e.stopPropagation();
                    if (!wasDragging) getWire().$call('editarPlanificacion', id);
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

        attachBars();
        document.addEventListener('livewire:updated', attachBars);
    }

    document.addEventListener('livewire:initialized', initCal);
})();
</script>
@endpush
