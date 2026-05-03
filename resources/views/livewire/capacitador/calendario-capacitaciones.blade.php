@section('header_title', 'Planificación de Capacitaciones')

@push('css')
<style>
    .planning-scroll::-webkit-scrollbar { height: 8px; width: 8px; }
    .planning-scroll::-webkit-scrollbar-track { background: transparent; }
    .planning-scroll::-webkit-scrollbar-thumb { background: #dbe3ef; border-radius: 999px; }
    .planning-shell { --planning-week-width: 9rem; }
    .planning-cell { min-width: var(--planning-week-width); }
    .planning-chip { transition: transform .12s ease, opacity .12s ease, box-shadow .12s ease, filter .12s ease; }
    .planning-chip:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -16px rgba(15, 23, 42, .55); }
    .cal-header, .cal-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
    [x-cloak] { display: none !important; }
</style>
@endpush

{{--
    Tailwind safelist for dynamic course palette:
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div
    x-data="weeklyPlanner({
        readonly: {{ $readonly ? 'true' : 'false' }},
        modoPlaneacion: @entangle('modoPlaneacion'),
        modoVista: @entangle('modoVista')
    })"
    @mouseup.window="finishPointerAction()"
    @mousemove.window="trackPointer($event)"
    @keydown.escape.window="cancelPointerAction()"
    class="planning-shell space-y-5"
>
    <template x-teleport="body">
        <div
            x-cloak
            x-show="pointerPreview.show"
            :style="`left: ${pointerPreview.x}px; top: ${pointerPreview.y}px; ${pointerPreview.width ? 'width: ' + pointerPreview.width + 'px;' : ''}`"
            :class="pointerPreview.isOutline
                ? 'pointer-events-none fixed z-[120] h-9 -translate-y-1/2 rounded-lg border-2 border-dashed border-Alumco-blue bg-Alumco-blue/10 shadow-sm backdrop-blur-sm'
                : 'pointer-events-none fixed z-[120] rounded-xl bg-Alumco-blue px-3 py-2 text-xs font-black uppercase tracking-wide text-white shadow-xl shadow-Alumco-blue/20 ring-1 ring-white/20'"
        >
            <span x-show="!pointerPreview.isOutline" x-text="pointerPreview.text"></span>
        </div>

        <div
            x-cloak
            x-show="courseTooltip.show"
            x-transition.opacity.duration.200ms
            :style="`left: ${courseTooltip.x}px; top: ${courseTooltip.y}px; transform: translateX(-50%);`"
            class="pointer-events-none fixed z-[130] max-w-xs rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white shadow-xl ring-1 ring-white/10"
        >
            <span x-text="courseTooltip.text"></span>
            <div class="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900"></div>
        </div>
    </template>

    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="text-2xl font-display font-black text-Alumco-blue tracking-tight">Editor de planificación</h2>
            <p class="mt-1 text-sm font-medium text-gray-500">Organiza cursos por semana y sede para el año académico.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center rounded-xl bg-white p-1 shadow-sm ring-1 ring-gray-100">
                <button wire:click="cambiarVista('anual')" @class([
                    'rounded-lg px-3 py-2 text-xs font-black uppercase tracking-widest transition',
                    'bg-Alumco-blue text-white shadow-sm' => $modoVista === 'anual',
                    'text-gray-500 hover:bg-gray-50' => $modoVista !== 'anual',
                ])>Anual</button>
                <button wire:click="cambiarVista('mensual')" @class([
                    'rounded-lg px-3 py-2 text-xs font-black uppercase tracking-widest transition',
                    'bg-Alumco-blue text-white shadow-sm' => $modoVista === 'mensual',
                    'text-gray-500 hover:bg-gray-50' => $modoVista !== 'mensual',
                ])>Mensual</button>
            </div>

            @if($esAdmin && $modoVista === 'anual')
                <button
                    wire:click="toggleModoPlaneacion"
                    @class([
                        'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-black transition shadow-sm ring-1',
                        'bg-Alumco-blue text-white ring-Alumco-blue' => $modoPlaneacion,
                        'bg-white text-Alumco-blue ring-gray-100 hover:ring-Alumco-blue/25' => ! $modoPlaneacion,
                    ])
                >
                    <span class="h-2 w-2 rounded-full {{ $modoPlaneacion ? 'bg-white animate-pulse' : 'bg-Alumco-blue' }}"></span>
                    {{ $modoPlaneacion ? 'Planificación activa' : 'Activar edición' }}
                </button>
            @endif
        </div>
    </div>

    @if($modoVista === 'anual')
        <div class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button wire:click="irAnioAnterior" class="rounded-xl bg-gray-50 px-3 py-2 text-sm font-black text-gray-500 hover:bg-gray-100">Anterior</button>
                    <div class="min-w-24 text-center">
                        <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400">Año</span>
                        <span class="text-xl font-display font-black text-Alumco-blue">{{ $anioActual }}</span>
                    </div>
                    <button wire:click="irAnioSiguiente" class="rounded-xl bg-gray-50 px-3 py-2 text-sm font-black text-gray-500 hover:bg-gray-100">Siguiente</button>

                    <div class="mx-1 hidden h-8 w-px bg-gray-100 sm:block"></div>

                    <button @click="scrollToToday()" class="rounded-xl bg-Alumco-blue/5 px-3 py-2 text-sm font-black text-Alumco-blue">Ir a hoy</button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Ir a mes</span>
                        <select @change="scrollToMonth($event.target.value)" class="bg-transparent text-sm font-black text-gray-600 outline-none">
                            <option value="">Seleccionar...</option>
                            @foreach($mesesHeaderVentana as $mInfo)
                                <option value="{{ $mInfo['semanaInicio'] }}">{{ $nombresMeses[$mInfo['mes'] - 1] }}</option>
                            @endforeach
                        </select>
                    </label>

                    @if($esAdmin)
                        <button wire:click="abrirModalCopiarAnio" class="rounded-xl bg-white px-4 py-2 text-sm font-black text-Alumco-blue shadow-sm ring-1 ring-Alumco-blue/15 transition hover:bg-Alumco-blue/5 hover:ring-Alumco-blue/30">
                            Copiar año
                        </button>
                    @endif
                </div>
            </div>

            @if($modoPlaneacion)
                <div class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                    Haz clic en una celda para crear un curso en una semana. Arrastra sobre varias semanas para crear un rango. En bloques existentes puedes mover, estirar bordes o eliminar.
                </div>
            @endif
        </div>

        <div @class([
            'grid gap-4',
            'xl:grid-cols-[18rem_minmax(0,1fr)]' => $esAdmin && $modoPlaneacion,
        ])>
            @if($esAdmin && $modoPlaneacion)
                <aside class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="border-b border-gray-100 p-4">
                        <h3 class="font-display text-lg font-black text-Alumco-blue">Cursos disponibles</h3>
                        <input
                            type="search"
                            wire:model.live.debounce.200ms="busquedaSidebar"
                            placeholder="Buscar curso"
                            class="mt-3 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-sm font-semibold text-Alumco-blue outline-none focus:border-Alumco-blue/30 focus:ring-4 focus:ring-Alumco-blue/10"
                        >

                        <div class="mt-3 flex items-center gap-1 rounded-xl bg-gray-50 p-1 ring-1 ring-gray-100">
                            <button
                                wire:click="$set('filtroSidebarEstado', 'pendientes')"
                                @class([
                                    'flex-1 rounded-lg py-1.5 text-[10px] font-black uppercase tracking-widest transition',
                                    'bg-white text-Alumco-blue shadow-sm' => $filtroSidebarEstado === 'pendientes',
                                    'text-gray-400 hover:text-gray-600' => $filtroSidebarEstado !== 'pendientes',
                                ])
                            >Pendientes</button>
                            <button
                                wire:click="$set('filtroSidebarEstado', 'todos')"
                                @class([
                                    'flex-1 rounded-lg py-1.5 text-[10px] font-black uppercase tracking-widest transition',
                                    'bg-white text-Alumco-blue shadow-sm' => $filtroSidebarEstado === 'todos',
                                    'text-gray-400 hover:text-gray-600' => $filtroSidebarEstado !== 'todos',
                                ])
                            >Todos</button>
                        </div>
                    </div>
                    <div class="planning-scroll max-h-[34rem] space-y-2 overflow-y-auto p-3">
                        @forelse($sidebarList as $curso)
                            <button
                                type="button"
                                wire:key="planner-course-{{ $curso['id'] }}"
                                draggable="true"
                                @dragstart="startCourseDrag({{ $curso['id'] }}, $event)"
                                @dragend="resetCourseDrag()"
                                @click="abrirCursoManual({{ $curso['id'] }})"
                                class="flex w-full cursor-grab select-none items-center gap-3 rounded-xl border border-gray-100 bg-white px-3 py-2 text-left transition hover:border-Alumco-blue/20 hover:bg-Alumco-blue/5 active:cursor-grabbing"
                            >
                                <span class="pointer-events-none h-3 w-3 shrink-0 rounded-full {{ $curso['bg'] }}"></span>
                                <span class="pointer-events-none truncate text-xs font-black uppercase tracking-tight text-gray-600">{{ $curso['titulo'] }}</span>
                            </button>
                        @empty
                            <p class="px-2 py-8 text-center text-xs font-black uppercase tracking-widest text-gray-300">
                                {{ $busquedaSidebar ? 'Sin resultados' : ($filtroSidebarEstado === 'pendientes' ? 'Sin cursos pendientes' : 'No hay cursos disponibles') }}
                            </p>
                        @endforelse
                    </div>
                </aside>
            @endif

            <section class="min-w-0 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div x-ref="calendarScroller" class="planning-scroll overflow-x-auto">
                    <div class="grid min-w-max" style="grid-template-columns: 12rem repeat({{ count($semanasVisibles) }}, var(--planning-week-width))">
                        {{-- Fila de Meses --}}
                        <div class="sticky left-0 z-50 border-b border-r border-gray-100 bg-white p-2 text-[10px] font-black uppercase tracking-widest text-gray-400">Mes</div>
                        @foreach($mesesHeaderVentana as $mesObj)
                            <div class="z-10 border-b border-r border-gray-100 bg-white p-2 text-center text-[10px] font-black uppercase tracking-widest text-Alumco-blue" style="grid-column: span {{ $mesObj['span'] }}">
                                {{ $nombresMeses[$mesObj['mes'] - 1] }}
                            </div>
                        @endforeach

                        <div class="sticky left-0 z-50 border-b border-r border-gray-100 bg-gray-50 p-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Sede / Semana</div>

                        @foreach($semanasVisibles as $semana)
                            <div
                                data-week-header="{{ $semana['numero'] }}"
                                data-week-today="{{ $semana['esHoy'] ? 'true' : 'false' }}"
                                @class([
                                'planning-cell border-b border-r border-gray-100 p-2 text-center',
                                'bg-Alumco-blue/10' => $semana['esHoy'],
                                'bg-gray-50' => ! $semana['esHoy'],
                            ])>
                                <span class="block text-[10px] font-black uppercase tracking-widest {{ $semana['esHoy'] ? 'text-Alumco-blue' : 'text-gray-400' }}">Semana</span>
                                <span class="text-base font-black {{ $semana['esHoy'] ? 'text-Alumco-blue' : 'text-gray-600' }}">{{ $semana['numero'] }}</span>
                                <span class="block text-[9px] font-bold text-gray-400">{{ \Carbon\Carbon::parse($semana['inicio'])->format('d/m') }} - {{ \Carbon\Carbon::parse($semana['fin'])->format('d/m') }}</span>
                            </div>
                        @endforeach

                        @foreach($filasAnuales as $fila)
                            @if($readonly && $fila['sede_id'] !== null && $fila['sede_id'] !== $userSedeId)
                                @continue
                            @endif

                            @php $sedeKey = $fila['sede_id'] ?? 0; @endphp

                            <div class="sticky left-0 z-20 flex min-h-24 items-center border-b border-r border-gray-100 bg-white p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-black uppercase tracking-tight text-Alumco-blue">{{ $fila['nombre'] }}</p>
                                    <p class="mt-1 text-[10px] font-bold text-gray-400">{{ $fila['sede_id'] ? 'Sede específica' : 'Plan global' }}</p>
                                </div>
                            </div>

                            @foreach($semanasVisibles as $semana)
                                @php
                                    $semanaNumero = $semana['numero'];
                                    $datosSemana = $fila['semanas'][$semanaNumero] ?? ['cursos' => [], 'conflicto' => false];
                                    $cursosInicio = collect($datosSemana['cursos'])->filter(fn ($curso) => $curso['esInicio'])->values();
                                @endphp

                                <div
                                    wire:key="planner-cell-{{ $sedeKey }}-{{ $semanaNumero }}"
                                    data-planner-cell
                                    data-week="{{ $semanaNumero }}"
                                    data-sede="{{ $sedeKey }}"
                                    @class([
                                        'planning-cell relative min-h-24 border-b border-r border-gray-100 p-1.5 transition',
                                        'cursor-pointer' => $esAdmin && $modoPlaneacion,
                                        'bg-amber-50' => $datosSemana['conflicto'],
                                        'bg-white' => ! $datosSemana['conflicto'] && ! $semana['esHoy'],
                                        'bg-Alumco-blue/[0.03]' => ! $datosSemana['conflicto'] && $semana['esHoy'],
                                    ])
                                    :class="cellClass({{ $semanaNumero }}, {{ $sedeKey }})"
                                    @mousedown="handleCellMouseDown($event, {{ $semanaNumero }}, {{ $sedeKey }})"
                                    @mouseenter="enterCell({{ $semanaNumero }}, {{ $sedeKey }})"
                                    @dragenter.prevent="enterCourseCell({{ $semanaNumero }}, {{ $sedeKey }}); updatePointerPreview($event)"
                                    @dragover.prevent="enterCourseCell({{ $semanaNumero }}, {{ $sedeKey }}); updatePointerPreview($event)"
                                    @drop.prevent="dropCourseOnCell({{ $semanaNumero }}, {{ $sedeKey }}, $event)"
                                    @click="handleCellClick($event, {{ $semanaNumero }}, {{ $sedeKey }})"
                                >
                                    @if($datosSemana['conflicto'])
                                        <span class="absolute right-1 top-1 rounded-full bg-amber-500 px-1.5 py-0.5 text-[9px] font-black text-white">!</span>
                                    @endif

                                    <div class="space-y-1.5">
                                        @foreach($cursosInicio as $curso)
                                            @php
                                                $duracion = max(1, $curso['semaFin'] - $curso['semaInicio'] + 1);
                                                // En scroll full year, el span visible es simplemente la duración
                                                $spanVisible = $duracion;
                                            @endphp
                                            <div
                                                wire:key="planner-chip-{{ $curso['id'] }}-{{ $sedeKey }}-{{ $semanaNumero }}"
                                                data-planning-chip
                                                :class="chipClass({{ $curso['id'] }})"
                                                class="planning-chip group relative z-10 flex h-9 items-center overflow-hidden rounded-lg text-white shadow-sm ring-1 ring-black/5 {{ $curso['bg'] }}"
                                                style="width: calc({{ $spanVisible }} * var(--planning-week-width) - .75rem)"
                                            >
                                                @if($esAdmin && $modoPlaneacion)
                                                    <button type="button" data-planning-control class="h-full w-2 cursor-ew-resize bg-white/20 hover:bg-white/40" @mousedown.stop.prevent="startResize({{ $curso['id'] }}, {{ $curso['semaInicio'] }}, 'inicio', $event)"></button>
                                                    <button
                                                        type="button"
                                                        data-planning-control
                                                        class="flex h-full w-7 cursor-grab items-center justify-center bg-black/10 text-white transition hover:bg-black/20 active:cursor-grabbing"
                                                        title="Mover planificación"
                                                        @mousedown.stop.prevent="startMove({{ $curso['id'] }}, {{ $curso['semaInicio'] }}, {{ $duracion - 1 }}, {{ $sedeKey }}, $event)"
                                                    >
                                                        <span class="pointer-events-none flex flex-col gap-0.5">
                                                            <span class="block h-0.5 w-3 rounded-full bg-white/90"></span>
                                                            <span class="block h-0.5 w-3 rounded-full bg-white/90"></span>
                                                            <span class="block h-0.5 w-3 rounded-full bg-white/90"></span>
                                                        </span>
                                                    </button>
                                                @endif

                                                <button
                                                    type="button"
                                                    class="min-w-0 flex-1 cursor-pointer px-2 text-left text-[10px] font-black uppercase tracking-tight hover:bg-white/10"
                                                    @mouseenter="showCourseTooltip($event, '{{ addslashes($curso['titulo']) }}')"
                                                    @mousemove="showCourseTooltip($event, '{{ addslashes($curso['titulo']) }}')"
                                                    @mouseleave="hideCourseTooltip()"
                                                    @if($esAdmin && $modoPlaneacion)
                                                        wire:click.stop="editarPlanificacion({{ $curso['id'] }})"
                                                    @endif
                                                >
                                                    <span class="block truncate">{{ $curso['titulo'] }}</span>
                                                </button>

                                                @if($esAdmin && $modoPlaneacion)
                                                    <button type="button" data-planning-control class="h-full w-7 bg-black/10 text-white opacity-0 transition hover:bg-red-600 group-hover:opacity-100" wire:click.stop="abrirModalBorrado({{ $curso['id'] }})">x</button>
                                                @endif

                                                @if($esAdmin && $modoPlaneacion)
                                                    <button type="button" data-planning-control class="h-full w-2 cursor-ew-resize bg-white/20 hover:bg-white/40" @mousedown.stop.prevent="startResize({{ $curso['id'] }}, {{ $curso['semaFin'] }}, 'fin', $event)"></button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    @else
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-4">
                <div class="flex items-center gap-2">
                    <button wire:click="mesAnterior" class="rounded-xl bg-gray-50 px-3 py-2 text-sm font-black text-gray-500 hover:bg-gray-100">Anterior</button>
                    <span class="min-w-44 text-center text-sm font-black uppercase tracking-widest text-Alumco-blue">
                        {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }} {{ $anioActual }}
                    </span>
                    <button wire:click="mesSiguiente" class="rounded-xl bg-gray-50 px-3 py-2 text-sm font-black text-gray-500 hover:bg-gray-100">Siguiente</button>
                </div>
                @if(!$esMesActual)
                    <button wire:click="irAHoy" class="rounded-xl bg-Alumco-blue/5 px-3 py-2 text-sm font-black text-Alumco-blue">Hoy</button>
                @endif
            </div>

            <div class="cal-header border-b border-gray-100 bg-gray-50">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                    <div class="p-3 text-center text-xs font-black uppercase tracking-widest text-gray-400">{{ $dia }}</div>
                @endforeach
            </div>

            @foreach($semanasDelMes as $semIdx => $semana)
                <div class="cal-week relative border-b border-gray-100 last:border-b-0" style="grid-template-rows: 34px repeat({{ $semana['maxSlot'] }}, 32px) 10px">
                    @foreach($semana['dias'] as $dIdx => $dia)
                        <div @class([
                            'min-h-28 border-r border-gray-100 p-2 last:border-r-0',
                            'bg-Alumco-blue/5' => $dia['esHoy'],
                            'bg-gray-50/50 text-gray-300' => ! $dia['esMesActual'],
                        ]) style="grid-column: {{ $dIdx + 1 }}; grid-row: 1 / -1">
                            <span class="text-xs font-black">{{ $dia['num'] }}</span>
                        </div>
                    @endforeach

                    @foreach($semana['barras'] as $barra)
                        <button
                            type="button"
                            wire:key="bar-cal-{{ $semIdx }}-{{ $barra['id'] }}"
                            wire:click="editarPlanificacion({{ $barra['id'] }})"
                            @mouseenter="showCourseTooltip($event, '{{ addslashes($barra['titulo']) }}')"
                            @mousemove="showCourseTooltip($event, '{{ addslashes($barra['titulo']) }}')"
                            @mouseleave="hideCourseTooltip()"
                            class="z-10 mx-1 h-7 truncate rounded-md px-2 text-left text-[10px] font-black uppercase tracking-tight text-white shadow-sm {{ $barra['bg'] }}"
                            style="grid-column: {{ $barra['col'] }} / span {{ $barra['span'] }}; grid-row: {{ $barra['slot'] + 2 }};"
                        >
                            {{ $barra['titulo'] }}
                        </button>
                    @endforeach
                </div>
            @endforeach
        </section>
    @endif

    @teleport('body')
        <div>
            @if($mostrarModalPlanificacion)
        <div class="fixed inset-0 z-[100] flex min-h-screen justify-end bg-Alumco-blue/25 backdrop-blur-sm" wire:click="cerrarModal">
            <aside class="flex h-screen w-full max-w-2xl flex-col bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="border-b border-gray-100 bg-Alumco-blue/5 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-display text-2xl font-black text-Alumco-blue">{{ $editandoId ? 'Editar bloque' : 'Nueva planificación' }}</h3>
                            <p class="mt-1 text-xs font-black uppercase tracking-widest text-Alumco-blue/40">Cursos por semana y sede</p>
                        </div>
                        <button wire:click="cerrarModal" class="rounded-xl bg-white px-3 py-2 text-sm font-black text-gray-400 shadow-sm ring-1 ring-gray-100">Cerrar</button>
                    </div>
                </div>

                <div class="planning-scroll flex-1 space-y-6 overflow-y-auto p-6">
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Curso</label>
                        <input type="search" wire:model.live.debounce.200ms="queryModal" placeholder="Buscar curso" class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10">
                        <div class="planning-scroll mt-2 max-h-48 space-y-1 overflow-y-auto rounded-xl bg-gray-50 p-2">
                            @forelse($modalList as $curso)
                                <button type="button" wire:click="seleccionarCurso({{ $curso['id'] }})" @class([
                                    'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-black uppercase tracking-tight transition',
                                    'bg-white text-Alumco-blue shadow-sm' => (int) $cursoId === $curso['id'],
                                    'text-gray-500 hover:bg-white/70' => (int) $cursoId !== $curso['id'],
                                ])>
                                    <span class="h-2.5 w-2.5 rounded-full {{ $curso['bg'] }}"></span>
                                    <span class="truncate">{{ $curso['titulo'] }}</span>
                                </button>
                            @empty
                                <p class="py-6 text-center text-xs font-black uppercase tracking-widest text-gray-300">Sin resultados</p>
                            @endforelse
                        </div>
                        @error('cursoId') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Sede</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('sedeIdPlan', null)" @class([
                                'rounded-xl px-3 py-2 text-xs font-black uppercase tracking-widest ring-1 transition',
                                'bg-Alumco-blue text-white ring-Alumco-blue' => $sedeIdPlan === null,
                                'bg-white text-gray-500 ring-gray-100' => $sedeIdPlan !== null,
                            ])>Todas</button>
                            @foreach($sedes as $sede)
                                <button type="button" wire:click="$set('sedeIdPlan', {{ $sede['id'] }})" @class([
                                    'rounded-xl px-3 py-2 text-xs font-black uppercase tracking-widest ring-1 transition',
                                    'bg-Alumco-blue text-white ring-Alumco-blue' => (int) $sedeIdPlan === $sede['id'],
                                    'bg-white text-gray-500 ring-gray-100' => (int) $sedeIdPlan !== $sede['id'],
                                ])>{{ $sede['nombre'] }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Inicio</span>
                            <input type="date" wire:model.live="fechaInicioPlan" class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Fin</span>
                            <input type="date" wire:model.live="fechaFinPlan" class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10">
                        </label>
                        @error('fechaInicioPlan') <p class="text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                        @error('fechaFinPlan') <p class="text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Notas</span>
                        <textarea wire:model="notas" rows="3" class="mt-2 w-full resize-none rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10"></textarea>
                    </label>
                </div>

                <div class="sticky bottom-0 flex gap-2 border-t border-gray-100 bg-white p-6">
                    <button wire:click="cerrarModal" class="flex-1 rounded-xl bg-gray-100 px-4 py-3 text-xs font-black uppercase tracking-widest text-gray-500">Cancelar</button>
                    <button wire:click="guardarPlanificacion" wire:loading.attr="disabled" class="flex-[2] rounded-xl bg-Alumco-blue px-4 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-Alumco-blue/20 disabled:opacity-50">
                        {{ $editandoId ? 'Guardar cambios' : 'Crear planificación' }}
                    </button>
                </div>
            </aside>
        </div>
            @endif

            @if($mostrarModalCopiarAnio)
        <div class="fixed inset-0 z-[100] flex min-h-screen items-center justify-center bg-black/45 p-4 backdrop-blur-sm" wire:click="cerrarModalCopiarAnio">
            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
                <div class="border-b border-gray-100 p-6">
                    <h3 class="font-display text-2xl font-black text-Alumco-blue">Copiar planificación anual</h3>
                    <p class="mt-1 text-sm font-semibold text-gray-500">Elige un año origen y cómo aplicar la copia en el año destino.</p>
                </div>

                <div class="space-y-4 p-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Año origen</span>
                            <input type="number" wire:model.live="anioOrigen" min="2020" max="2099" class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10">
                        </label>

                        <label class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Año destino</span>
                            <input type="number" wire:model.live="anioDestino" min="2020" max="2099" class="mt-2 w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-bold text-Alumco-blue outline-none focus:ring-4 focus:ring-Alumco-blue/10">
                        </label>
                    </div>
                    @error('anioOrigen') <p class="text-sm font-bold text-red-600">{{ $message }}</p> @enderror
                    @error('anioDestino') <p class="text-sm font-bold text-red-600">{{ $message }}</p> @enderror

                    @if($anioDestinoTienePlanificaciones)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                            El año {{ $anioDestino }} ya tiene planificaciones. Puedes añadir los cursos de {{ $anioOrigen }} o reemplazar primero la planificación del destino.
                        </div>
                    @else
                        <p class="text-sm font-medium text-gray-500">El año {{ $anioDestino }} no tiene planificaciones solapadas. La copia se puede aplicar directamente desde {{ $anioOrigen }}.</p>
                    @endif
                </div>

                <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 bg-gray-50 p-4">
                    <button wire:click="cerrarModalCopiarAnio" class="rounded-xl bg-white px-4 py-2 text-sm font-black text-gray-500 ring-1 ring-gray-100">Cancelar</button>

                    @if($anioDestinoTienePlanificaciones)
                        <button wire:click="copiarAnio('append')" wire:loading.attr="disabled" class="rounded-xl bg-Alumco-blue/10 px-4 py-2 text-sm font-black text-Alumco-blue disabled:opacity-50">Añadir al año objetivo</button>
                        <button wire:click="copiarAnio('replace')" wire:loading.attr="disabled" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-black text-white disabled:opacity-50">Reemplazar destino</button>
                    @else
                        <button wire:click="copiarAnio" wire:loading.attr="disabled" class="rounded-xl bg-Alumco-blue px-4 py-2 text-sm font-black text-white disabled:opacity-50">Copiar</button>
                    @endif
                </div>
            </div>
        </div>
            @endif

            @if($mostrarModalBorrado)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-black/45 p-4 backdrop-blur-sm" wire:click="cerrarModalBorrado">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl" onclick="event.stopPropagation()">
                <h3 class="font-display text-xl font-black text-Alumco-blue">Eliminar planificación</h3>
                <p class="mt-2 text-sm font-semibold text-gray-500">Esta acción eliminará el bloque del calendario.</p>
                <div class="mt-6 flex justify-end gap-2">
                    <button class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-black text-gray-500" wire:click="cerrarModalBorrado">Cancelar</button>
                    <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white" wire:click="confirmarBorrado" wire:loading.attr="disabled">Eliminar</button>
                </div>
            </div>
        </div>
            @endif
        </div>
    @endteleport
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('weeklyPlanner', (config) => ({
            readonly: config.readonly,
            modoPlaneacion: config.modoPlaneacion,
            modoVista: config.modoVista,
            action: null,
            startWeek: null,
            currentWeek: null,
            startSede: null,
            currentSede: null,
            planId: null,
            draggedCourseId: null,
            span: 0,
            edge: null,
            moved: false,
            suppressNextClick: false,
            pointerPreview: {
                show: false,
                isOutline: false,
                width: null,
                x: 0,
                y: 0,
                text: '',
            },
            courseTooltip: {
                show: false,
                x: 0,
                y: 0,
                text: '',
            },

            showCourseTooltip(event, text) {
                if (this.action) return;
                this.courseTooltip.show = true;
                this.courseTooltip.text = text;
                this.courseTooltip.x = event.clientX;
                this.courseTooltip.y = event.clientY - 35;
            },

            hideCourseTooltip() {
                this.courseTooltip.show = false;
            },

            canEdit() {
                return !this.readonly && this.modoPlaneacion && this.modoVista === 'anual';
            },

            abrirCursoManual(courseId) {
                if (!this.canEdit()) return;
                this.$wire.abrirModalConCurso(courseId);
            },

            startCourseDrag(courseId, event) {
                if (!this.canEdit()) return;
                this.action = 'course';
                this.draggedCourseId = courseId;
                this.startWeek = null;
                this.currentWeek = null;
                this.startSede = null;
                this.currentSede = null;
                this.moved = false;
                this.updatePointerPreview(event, 'Arrastra a una semana');

                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'copy';
                    event.dataTransfer.setData('text/plain', String(courseId));
                    event.dataTransfer.setData('application/x-alumco-course-id', String(courseId));
                }
            },

            enterCourseCell(week, sede) {
                if (!this.canEdit() || this.action !== 'course') return;

                // Al arrastrar un curso externo, siempre seleccionamos solo una semana por defecto
                this.startWeek = week;
                this.currentWeek = week;
                this.startSede = sede;
                this.currentSede = sede;
            },

            dropCourseOnCell(week, sede, event) {
                if (!this.canEdit()) return;

                const transferredCourseId = event.dataTransfer?.getData('application/x-alumco-course-id')
                    || event.dataTransfer?.getData('text/plain')
                    || this.draggedCourseId;
                const courseId = parseInt(transferredCourseId);

                if (!courseId) return;

                if (this.startWeek === null || this.startSede !== sede) {
                    this.startWeek = week;
                    this.startSede = sede;
                }

                this.currentWeek = week;
                this.currentSede = sede;

                // Para arrastrar cursos externos, guardamos directamente sin abrir modal
                this.$wire.guardarPlanificacionRapidaAnual(courseId, this.currentWeek, this.startSede);
                this.resetCourseDrag();
            },

            resetCourseDrag() {
                if (this.action !== 'course') return;
                this.resetPointer();
                this.draggedCourseId = null;
            },

            shouldIgnoreCellEvent(event) {
                return Boolean(event.target.closest('[data-planning-chip], [data-planning-control]'));
            },

            handleCellMouseDown(event, week, sede) {
                if (this.shouldIgnoreCellEvent(event)) return;
                event.preventDefault();
                this.startCreate(week, sede, event);
            },

            handleCellClick(event, week, sede) {
                if (this.shouldIgnoreCellEvent(event)) return;
                this.clickCreate(week, sede);
            },

            clickCreate(week, sede) {
                if (!this.canEdit() || this.suppressNextClick) {
                    this.suppressNextClick = false;
                    return;
                }

                this.$wire.abrirModalAnualSemana(week, sede);
            },

            startCreate(week, sede, event = null) {
                if (!this.canEdit()) return;
                this.action = 'create';
                this.startWeek = week;
                this.currentWeek = week;
                this.startSede = sede;
                this.currentSede = sede;
                this.moved = false;
                document.body.style.cursor = 'crosshair';
                document.body.style.userSelect = 'none';
                this.updatePointerPreview(event, 'Nuevo bloque');
            },

            startMove(id, week, span, sede, event = null) {
                if (!this.canEdit()) return;
                this.action = 'move';
                this.planId = id;
                this.startWeek = week;
                this.currentWeek = week;
                this.startSede = sede;
                this.currentSede = sede;
                this.span = span;
                this.moved = false;
                document.body.style.cursor = 'grabbing';
                document.body.style.userSelect = 'none';
                this.updatePointerPreview(event, 'Mover bloque');
            },

            startResize(id, week, edge, event = null) {
                if (!this.canEdit()) return;
                this.action = 'resize';
                this.planId = id;
                this.startWeek = week;
                this.currentWeek = week;
                this.edge = edge;
                this.moved = false;
                document.body.style.cursor = 'ew-resize';
                document.body.style.userSelect = 'none';
                this.updatePointerPreview(event, edge === 'inicio' ? 'Ajustar inicio' : 'Ajustar fin');
            },

            enterCell(week, sede) {
                if (!this.action || this.action === 'course') return;
                this.currentWeek = week;
                this.currentSede = sede;
                const sedeChanged = this.action !== 'resize' && sede !== this.startSede;
                this.moved = this.moved || week !== this.startWeek || sedeChanged;
            },

            trackPointer(event) {
                if (!this.action) return;
                this.updatePointerPreview(event);
                if (this.action === 'course') return;
                const cell = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-planner-cell]');
                if (!cell) return;
                this.enterCell(parseInt(cell.dataset.week), parseInt(cell.dataset.sede));
            },

            finishPointerAction() {
                if (!this.action) return;

                if (this.action === 'create') {
                    const start = Math.min(this.startWeek, this.currentWeek);
                    const end = Math.max(this.startWeek, this.currentWeek);
                    if (this.moved) {
                        this.$wire.abrirModalAnualRango(start, end, this.startSede);
                    }
                }

                if (this.action === 'move' && this.moved) {
                    this.$wire.moverPlanificacionSemanas(this.planId, this.currentWeek, this.currentSede);
                }

                if (this.action === 'resize' && this.currentWeek !== this.startWeek) {
                    this.$wire.ajustarBordePlanificacionSemana(this.planId, this.edge, this.currentWeek);
                }

                this.suppressNextClick = this.moved;
                this.resetPointer();
            },

            cancelPointerAction() {
                if (this.action) {
                    this.resetPointer();
                    return;
                }

                if (this.modoPlaneacion) {
                    this.modoPlaneacion = false;
                }
            },

            resetPointer() {
                this.action = null;
                this.startWeek = null;
                this.currentWeek = null;
                this.startSede = null;
                this.currentSede = null;
                this.planId = null;
                this.draggedCourseId = null;
                this.span = 0;
                this.edge = null;
                this.pointerPreview.show = false;
                this.pointerPreview.isOutline = false;
                this.pointerPreview.width = null;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                window.setTimeout(() => { this.moved = false; }, 0);
            },

            scrollToMonth(weekNumber) {
                if (!weekNumber) return;
                const container = this.$refs.calendarScroller;
                const weekCell = container?.querySelector(`[data-week-header="${weekNumber}"], [data-week="${weekNumber}"]`);
                if (container && weekCell) {
                    const offset = weekCell.offsetLeft - 192; // 192px is 12rem (the sticky sidebar width)
                    container.scrollTo({ left: offset, behavior: 'smooth' });
                }
            },

            scrollToToday() {
                const container = this.$refs.calendarScroller;
                const todayCell = container?.querySelector('[data-week-today="true"]');
                if (container && todayCell) {
                    const offset = todayCell.offsetLeft - 192;
                    container.scrollTo({ left: offset, behavior: 'smooth' });
                }
            },

            updatePointerPreview(event, fallbackText = null) {
                if (!event || !this.action) return;

                const labels = {
                    create: 'Nuevo bloque',
                    move: 'Mover bloque',
                    resize: this.edge === 'inicio' ? 'Ajustar inicio' : 'Ajustar fin',
                    course: 'Soltar curso',
                };

                const isOutline = this.action === 'move';
                const spanWeeks = this.span + 1;
                // 9rem is 144px. 0.75rem is 12px.
                const outlineWidth = isOutline ? (spanWeeks * 144 - 12) : null;

                let x = event.clientX + 14;
                let y = event.clientY + 14;

                if (isOutline) {
                    // El centrado vertical se maneja con translate-y-1/2
                    // El pivot horizontal ahora es a la izquierda con un pequeño offset para no tapar el cursor
                    x = event.clientX + 8;
                    y = event.clientY;
                }

                this.pointerPreview = {
                    show: true,
                    isOutline: isOutline,
                    width: outlineWidth,
                    x: x,
                    y: y,
                    text: fallbackText || labels[this.action] || 'Planificando',
                };
            },

            chipClass(id) {
                if ((this.action === 'move' || this.action === 'resize') && this.planId === id) {
                    return 'scale-[0.98] opacity-45 ring-2 ring-white/80 brightness-110';
                }

                return '';
            },

            cellClass(week, sede) {
                if (!this.action) return '';

                if (this.action === 'course' && this.startSede === sede) {
                    const start = Math.min(this.startWeek, this.currentWeek);
                    const end = Math.max(this.startWeek, this.currentWeek);
                    return week >= start && week <= end ? 'bg-Alumco-blue/10 ring-2 ring-inset ring-Alumco-blue/20' : '';
                }

                if (this.action === 'create' && this.startSede === sede) {
                    const start = Math.min(this.startWeek, this.currentWeek);
                    const end = Math.max(this.startWeek, this.currentWeek);
                    return week >= start && week <= end ? 'bg-Alumco-blue/10 ring-2 ring-inset ring-Alumco-blue/20' : '';
                }

                if (this.action === 'move' && this.currentSede === sede) {
                    return week >= this.currentWeek && week <= this.currentWeek + this.span ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-200' : '';
                }

                if (this.action === 'resize') {
                    return week === this.currentWeek ? 'bg-Alumco-blue/10 ring-2 ring-inset ring-Alumco-blue/20' : '';
                }

                return '';
            },
        }));
    });
</script>
@endpush
