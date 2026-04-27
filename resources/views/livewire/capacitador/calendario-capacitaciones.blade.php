@section('header_title', 'Calendario de Eventos')

@push('css')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

    .cal-bar { transition: transform 0.1s ease, box-shadow 0.1s ease, filter 0.1s ease; }
    .cal-bar:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    
    .plan-mode-active .cal-day-cell:hover { background-color: rgba(32, 80, 153, 0.02); cursor: cell; }
    
    [x-cloak] { display: none !important; }

    /* Modal animations */
    #planning-modal .transform { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
</style>
@endpush

{{--
    Tailwind safelist — dynamic course-palette classes (do not remove):
    bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
    bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
--}}
<div id="cal-root"
     x-data="calendarManager({
        modoVista: @entangle('modoVista'),
        modoPlaneacion: @entangle('modoPlaneacion'),
        mostrarModalPlanificacion: @entangle('mostrarModalPlanificacion'),
        readonly: {{ $readonly ? 'true' : 'false' }},
        diasEnMes: {{ $diasEnMes }},
        userSexo: '{{ $userSexo }}'
     })"
     @class(['relative', 'plan-mode-active' => $modoPlaneacion, 'cal-mode-readonly' => $readonly])>

    {{-- Floating tooltip during drag / resize / move --}}
    <div x-show="tooltip.show"
         x-text="tooltip.text"
         :style="`left: ${tooltip.x}px; top: ${tooltip.y}px;`"
         class="fixed pointer-events-none z-[9999] bg-Alumco-blue/90 text-white text-[11px] font-bold px-2 py-1 rounded-md shadow-lg whitespace-nowrap transition-none"
         x-cloak>
    </div>

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Programación de Sesiones</h2>
    </div>

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

            {{-- Sede filter (Multi-select) --}}
            @if($modoVista === 'mensual')
                <div class="relative flex items-center gap-2 mr-2" x-data="{ open: false }">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Filtrar Sedes:</span>
                    
                    <button @click="open = !open" 
                            class="flex items-center gap-2 text-sm font-bold text-Alumco-blue bg-white border border-gray-100 rounded-xl px-3 py-1.5 outline-none focus:ring-4 focus:ring-Alumco-blue/5 transition-all shadow-sm min-w-40 justify-between">
                        <span class="truncate max-w-32">
                            @if(empty($filtroSedesIds))
                                Todas las sedes
                            @elseif(count($filtroSedesIds) === 1)
                                {{ collect($sedes)->firstWhere('id', $filtroSedesIds[0])['nombre'] ?? '1 seleccionada' }}
                            @else
                                {{ count($filtroSedesIds) }} seleccionadas
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown list --}}
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-full left-0 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl z-[60] py-2 overflow-hidden">
                        
                        <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seleccionar</span>
                            @if(!empty($filtroSedesIds))
                                <button wire:click="$set('filtroSedesIds', [])" @click="open = false" class="text-[10px] font-bold text-red-500 hover:text-red-600">Limpiar</button>
                            @endif
                        </div>

                        <div class="max-h-60 overflow-y-auto py-1">
                            @foreach($sedes as $s)
                                @php $isSelected = in_array($s['id'], $filtroSedesIds); @endphp
                                <button wire:click="filtrarPorSede({{ $s['id'] }})"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 {{ $isSelected ? 'bg-Alumco-blue/5 text-Alumco-blue font-bold' : 'text-gray-600' }}">
                                    <div class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors {{ $isSelected ? 'bg-Alumco-blue border-Alumco-blue' : 'bg-white border-gray-300' }}">
                                        @if($isSelected)
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="truncate">{{ $s['nombre'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto overflow-y-visible pb-4 custom-scrollbar">
                        <div class="grid min-w-full w-max"
                             style="grid-template-columns: 200px repeat({{ $nSemanas }}, 80px); --cal-n-semanas: {{ $nSemanas }}">

                            {{-- ── Fila 1: Mes headers ──────────────── --}}
                            <div class="sticky left-0 z-30 bg-gray-50 border-r-2 border-gray-200 border-b-2 p-2 flex items-center">
                                <span class="text-gray-400 text-[10px] font-black uppercase tracking-tighter">Meses</span>
                            </div>
                            @foreach($mesesDelAnio as $mesIdx => $mes)
                                <div @class([
                                        'p-2 text-center text-xs font-black text-gray-700 border-b-2 border-r-2 border-gray-200 last:border-r-0 uppercase tracking-tight',
                                        $mesIdx % 2 === 0 ? 'bg-gray-50' : 'bg-blue-50/30'
                                     ])
                                     style="grid-column: span {{ $mes['span'] }}">
                                    {{ $mes['nombre'] }}
                                </div>
                            @endforeach

                            {{-- ── Fila 2: Número de semana ──────────── --}}
                            <div class="sticky left-0 z-30 bg-gray-50 border-r-2 border-gray-200 border-b p-2 flex items-center">
                                <span class="text-gray-400 text-[10px] font-black uppercase tracking-tighter">Semana</span>
                            </div>
                            @foreach($semanasDelAnio as $sem)
                                <div @class([
                                    'p-1 text-center text-[11px] font-black border-r border-b border-gray-100 last:border-r-0',
                                    'bg-Alumco-blue text-white' => $sem['esHoy'],
                                    'bg-white text-gray-500'    => ! $sem['esHoy'],
                                ])>
                                    {{ $sem['numero'] }}
                                </div>
                            @endforeach

                            {{-- ── Fila 3: Rango de fechas ───────────── --}}
                            <div class="sticky left-0 z-30 bg-gray-50 border-r-2 border-gray-200 border-b-2 p-2 flex items-center">
                                <span class="text-gray-400 text-[10px] font-black uppercase tracking-tighter">Días</span>
                            </div>
                            @foreach($semanasDelAnio as $sem)
                                <div class="p-1 text-center text-[10px] leading-tight text-gray-400 border-r border-b-2 border-gray-100 last:border-r-0 bg-gray-50/30 font-bold">
                                    {{ \Carbon\Carbon::parse($sem['inicio'])->format('d') }}<br>
                                    <span class="opacity-50 font-normal">{{ \Carbon\Carbon::parse($sem['fin'])->format('d') }}</span>
                                </div>
                            @endforeach

                            {{-- ── Filas: una por sede ─────────────────── --}}
                            @foreach($filasAnuales as $fila)
                                @if($readonly && $fila['sede_id'] !== null && $fila['sede_id'] !== $userSedeId)
                                    @continue
                                @endif

                                @php $sedeKey = $fila['sede_id'] ?? 0; @endphp

                                {{-- Row label (sticky) --}}
                                <div class="sticky left-0 z-30 bg-white border-r-2 border-gray-200 border-b border-gray-100 p-2 flex items-center gap-2 min-h-[44px] group select-none">
                                    @if($fila['sede_id'] === null)
                                        <svg class="w-3.5 h-3.5 text-Alumco-blue shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                        </svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    @endif
                                    <span class="truncate text-[11px] font-black uppercase tracking-tight text-gray-700">{{ $fila['nombre'] }}</span>
                                </div>

                                {{-- Week cells for this sede --}}
                                @foreach($semanasDelAnio as $sem)
                                    @php
                                        $semData     = $fila['semanas'][$sem['numero']] ?? ['cursos' => [], 'conflicto' => false];
                                        $tieneCursos = count($semData['cursos']) > 0;
                                    @endphp
                                    <div @class([
                                             'min-h-[44px] p-1 border-r border-b border-gray-100 last:border-r-0 relative flex flex-col gap-1 transition-all select-none',
                                             'bg-gray-50/30' => $sem['esPasada'] && ! $sem['esHoy'],
                                             'bg-blue-50/20' => $sem['esHoy'],
                                         ])
                                         :class="{
                                            'bg-Alumco-blue/5 z-10': isHovered({{ $sem['numero'] }}, {{ $sedeKey }}),
                                            'bg-Alumco-blue/[0.08] ring-1 ring-Alumco-blue/10': isAnnualCellHighlighted({{ $sem['numero'] }}, {{ $sedeKey }}) || isAnnualMoveTarget({{ $sem['numero'] }}, {{ $sedeKey }})
                                         }"
                                         @mouseenter="setHover({{ $sem['numero'] }}, {{ $sedeKey }})"
                                         @mouseleave="clearHover()"
                                         data-semana="{{ $sem['numero'] }}"
                                         data-sede-id="{{ $sedeKey }}"
                                         @mousedown.prevent="startAnnualDragCreate({{ $sem['numero'] }}, {{ $sedeKey }})"
                                         @mouseover="updateAnnualAction({{ $sem['numero'] }}, {{ $sedeKey }}, $event)"
                                         @mouseup="endAnnualAction()">

                                        @foreach($semData['cursos'] as $curso)
                                            <div @class([
                                                    'flex items-center h-6 overflow-hidden cursor-pointer transition-all shadow-sm ring-1 ring-black/5',
                                                    $curso['bg'],
                                                    'rounded-md' => $curso['esInicio'] && $curso['esFin'],
                                                    'rounded-l-md' => $curso['esInicio'] && ! $curso['esFin'],
                                                    'rounded-r-md' => ! $curso['esInicio'] && $curso['esFin'],
                                                 ])
                                                 draggable="false"
                                                 :class="draggedEventId === {{ $curso['id'] }} ? 'opacity-40 scale-[0.98] z-20' : 'z-10'"
                                                 wire:key="chip-{{ $sedeKey }}-{{ $sem['numero'] }}-{{ $curso['id'] }}"
                                                 @click.stop="if(!isDragging && !isResizing) $wire.editarPlanificacion({{ $curso['id'] }})">

                                                {{-- Left resize handle --}}
                                                @if($esAdmin && $modoPlaneacion && $curso['esInicio'])
                                                    <div class="w-1.5 h-full bg-white/20 hover:bg-white/40 cursor-ew-resize shrink-0"
                                                         @mousedown.stop.prevent="startAnnualResize({{ $curso['id'] }}, {{ $curso['semaInicio'] }}, 'inicio', $event)"></div>
                                                @endif

                                                {{-- Move zone + label --}}
                                                @if($esAdmin && $modoPlaneacion)
                                                    <div class="flex-1 min-w-0 flex items-center h-full px-1.5 cursor-grab active:cursor-grabbing"
                                                         @mousedown.stop.prevent="startAnnualMove({{ $curso['id'] }}, {{ $curso['semaInicio'] }}, {{ $curso['semaFin'] - $curso['semaInicio'] }}, {{ $sedeKey }}, $event)">
                                                        @if($curso['esInicio'])
                                                            <span class="text-white text-[10px] font-black uppercase tracking-tight truncate leading-none">
                                                                {{ $curso['titulo'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif($curso['esInicio'])
                                                    <span class="flex-1 px-1.5 text-white text-[10px] font-black uppercase tracking-tight truncate leading-none">
                                                        {{ $curso['titulo'] }}
                                                    </span>
                                                @endif

                                                {{-- Delete button --}}
                                                @if($esAdmin && $curso['esFin'])
                                                    <button type="button"
                                                            @click.stop="confirmDelete({{ $curso['id'] }})"
                                                            class="w-5 h-full flex items-center justify-center bg-black/10 hover:bg-black/20 text-white text-sm font-bold transition-colors">
                                                        &times;
                                                    </button>
                                                @endif

                                                {{-- Right resize handle --}}
                                                @if($esAdmin && $modoPlaneacion && $curso['esFin'])
                                                    <div class="w-1.5 h-full bg-white/20 hover:bg-white/40 cursor-ew-resize shrink-0"
                                                         @mousedown.stop.prevent="startAnnualResize({{ $curso['id'] }}, {{ $curso['semaFin'] }}, 'fin', $event)"></div>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if(! $readonly && $semData['conflicto'])
                                            <div class="absolute top-1 right-1 w-4 h-4 bg-amber-500 text-white rounded-full flex items-center justify-center text-[10px] font-black shadow-sm ring-2 ring-white z-20 cursor-help group/conflict"
                                                 title="Solapamiento de eventos">
                                                !
                                                {{-- Interactive Popover --}}
                                                <div class="hidden group-hover/conflict:block absolute bottom-full right-0 mb-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 p-2 z-[60]">
                                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 border-b border-gray-50 pb-1">Cursos en conflicto:</p>
                                                    <div class="space-y-1">
                                                        @foreach($semData['cursos_popover'] ?? $semData['cursos'] as $c)
                                                            <div class="flex items-center gap-2 p-1 rounded hover:bg-gray-50 transition-colors"
                                                                 @click.stop="$wire.editarPlanificacion({{ $c['id'] }})">
                                                                <div class="w-2 h-2 rounded-full {{ $c['bg'] }} shrink-0"></div>
                                                                <span class="text-[10px] font-bold text-gray-700 truncate">{{ $c['titulo'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                            @endforeach

                        </div>
                    </div>
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
                        <div class="cal-week relative border-b border-gray-100 last:border-0" 
                             style="grid-template-rows: 36px repeat({{ $semana['maxSlot'] }}, 32px) 8px">

                            {{-- Day backgrounds (using grid placement for perfect alignment) --}}
                            @foreach($semana['dias'] as $dIdx => $diaInfo)
                                <div @class([
                                         'border-r border-gray-100 last:border-0 transition-colors',
                                         'bg-Alumco-blue/5' => $diaInfo['esHoy'],
                                         'opacity-40'       => ! $diaInfo['esMesActual'],
                                         'bg-gray-50/50'    => $diaInfo['esWeekend'] && $diaInfo['esMesActual'],
                                     ])
                                     :class="isDayHighlighted({{ $diaInfo['num'] }}) ? 'bg-Alumco-blue/10' : ''"
                                     style="grid-column: {{ $dIdx + 1 }}; grid-row: 1 / -1;"
                                     @if($diaInfo['esMesActual'])
                                         data-day="{{ $diaInfo['num'] }}"
                                         @mousedown="startDragCreate({{ $diaInfo['num'] }})"
                                         @mouseover="updateDragCreate({{ $diaInfo['num'] }})"
                                         @mouseup="endDragCreate()"
                                     @endif>
                                </div>
                            @endforeach

                            {{-- Day numbers (fixed row 1, aligned) --}}
                            @foreach($semana['dias'] as $dIdx => $diaInfo)
                                <div class="relative z-10 flex justify-start items-start p-2 pointer-events-none select-none"
                                     style="grid-column: {{ $dIdx + 1 }}; grid-row: 1">
                                    <span @class([
                                        'text-white bg-Alumco-blue ring-4 ring-white shadow-sm rounded-full w-6 h-6 flex items-center justify-center text-[10px] font-black' => $diaInfo['esHoy'],
                                        'text-[11px] font-bold text-gray-300' => ! $diaInfo['esHoy'] && ! $diaInfo['esMesActual'],
                                        'text-[11px] font-black text-gray-500' => ! $diaInfo['esHoy'] && $diaInfo['esMesActual'],
                                    ])>
                                        {{ $diaInfo['num'] }}
                                    </span>
                                </div>
                            @endforeach

                            {{-- Course bars --}}
                            @foreach($semana['barras'] as $barra)
                                @php 
                                    $esPrimerSegmento = $barra['roundLeft'] || $barra['extiendePorIzq']; 
                                    $esPlanificable = $esAdmin && $modoPlaneacion;
                                    $diaIni = \Carbon\Carbon::parse($barra['fechaIni'])->day;
                                @endphp
                                <div wire:key="bar-cal-{{ $semIdx }}-{{ $barra['id'] }}"
                                     @class([
                                         'cal-bar group/bar transition-all shadow-sm ring-1 ring-black/5',
                                         $barra['bg'],
                                         'rounded-md' => $barra['roundLeft'] && $barra['roundRight'],
                                         'rounded-l-md' => $barra['roundLeft'] && ! $barra['roundRight'],
                                         'rounded-r-md' => ! $barra['roundLeft'] && $barra['roundRight'],
                                         'cal-bar-continuation' => ! $esPrimerSegmento,
                                         'cursor-grab active:cursor-grabbing' => $esPlanificable,
                                         'cursor-pointer'       => ! $esPlanificable,
                                     ])
                                     :class="draggedEventId === {{ $barra['id'] }} ? 'opacity-40 scale-[0.97] z-50' : 'z-20'"
                                     style="grid-column: {{ $barra['col'] }} / span {{ $barra['span'] }}; grid-row: {{ $barra['slot'] + 2 }}; height: 26px; margin-top: 2px;"
                                     @click.stop="if(!isDragging && !isResizing) $wire.editarPlanificacion({{ $barra['id'] }})">

                                    {{-- Left resize handle --}}
                                    @if($esPlanificable && $barra['roundLeft'])
                                        <div class="absolute left-0 top-0 bottom-0 w-2 cursor-ew-resize hover:bg-white/30 z-30 rounded-l-md"
                                             @mousedown.stop="startResize({{ $barra['id'] }}, {{ $diaIni }}, 'inicio', '{{ $barra['sede_nombre'] ?? '' }}', $event)"></div>
                                    @endif

                                    {{-- Move handle (middle zone) --}}
                                    <div class="cal-bar-content flex justify-center items-center h-full relative px-2"
                                         @if($esPlanificable) @mousedown.stop="startMove({{ $barra['id'] }}, {{ $diaIni }}, {{ $barra['span'] }}, '{{ $barra['sede_nombre'] ?? '' }}', $event)" @endif>
                                        
                                        @if($barra['extiendePorIzq'])
                                            <svg class="w-2.5 h-2.5 shrink-0 mr-1 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif

                                        @if($esPrimerSegmento)
                                            <span class="cal-bar-title font-black text-[10px] text-white drop-shadow-sm truncate pointer-events-none select-none uppercase tracking-tight">
                                                {{ $barra['titulo'] }}
                                                @if($barra['sede_nombre'])
                                                    <span class="opacity-75 font-normal ml-1">[{{ $barra['sede_nombre'] }}]</span>
                                                @endif
                                            </span>
                                        @endif

                                        @if($barra['extiendePorDer'])
                                            <svg class="w-2.5 h-2.5 shrink-0 ml-1 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif

                                        {{-- Delete button (admin only, on hover) --}}
                                        @if($esAdmin && $barra['roundRight'])
                                            <button type="button" 
                                                    @click.stop="confirmDelete({{ $barra['id'] }})"
                                                    class="hidden group-hover/bar:flex absolute right-0 top-0 bottom-0 px-2 bg-black/10 hover:bg-black/20 text-white items-center justify-center rounded-r-md transition-colors"
                                                    title="Eliminar">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Right resize handle --}}
                                    @if($esPlanificable && $barra['roundRight'])
                                        <div class="absolute right-0 top-0 bottom-0 w-2 cursor-ew-resize hover:bg-white/30 z-30 rounded-r-md"
                                             @mousedown.stop="startResize({{ $barra['id'] }}, {{ $diaIni + $barra['span'] - 1 }}, 'fin', '{{ $barra['sede_nombre'] ?? '' }}', $event)"></div>
                                    @endif
                                </div>
                            @endforeach

                        </div>{{-- /.cal-week --}}
                    @endforeach

                </div>{{-- /.bg-white monthly container --}}

            @endif

        </div>{{-- /.flex-1 --}}

    </div>{{-- /main layout --}}

    {{-- ── Planning modal ─────────────────────────────────────────────── --}}
    @if($mostrarModalPlanificacion)
        <div id="planning-modal"
             class="fixed inset-0 bg-Alumco-blue/20 z-50 flex justify-center items-center backdrop-blur-md transition-all duration-300"
             wire:click="cerrarModal">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden mx-4 transform transition-all"
                 onclick="event.stopPropagation()">

                {{-- Modal header --}}
                <div class="bg-white px-8 pt-8 pb-4 flex justify-between items-center">
                    <div>
                        <h3 class="font-display font-black text-2xl text-Alumco-blue tracking-tight">
                            {{ $editandoId ? 'Editar Periodo' : 'Nueva Planificación' }}
                        </h3>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                            {{ $editandoId ? 'Actualiza los detalles de esta capacitación' : 'Programa una nueva cápsula en el calendario' }}
                        </p>
                    </div>
                    <button wire:click="cerrarModal"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all text-2xl leading-none">&times;</button>
                </div>

                {{-- Modal body --}}
                <div class="px-8 py-6 space-y-6">

                    {{-- Course search + picker --}}
                    <div class="space-y-3">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Seleccionar Curso</label>

                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400 group-focus-within:text-Alumco-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.200ms="queryModal"
                                   placeholder="Buscar cápsula o curso por nombre..."
                                   class="w-full bg-gray-50 border-none rounded-2xl py-3.5 pl-11 pr-4 text-sm font-bold text-Alumco-blue placeholder:text-gray-400 focus:ring-4 focus:ring-Alumco-blue/10 transition-all"
                                   autocomplete="off">
                        </div>

                        <div class="modal-course-list bg-gray-50 rounded-2xl p-2 max-h-44 overflow-y-auto custom-scrollbar border border-gray-100/50">
                            @forelse($modalList as $curso)
                                @php $isSelected = ($cursoId == $curso['id']); @endphp
                                <div wire:click="seleccionarCurso({{ $curso['id'] }})"
                                     wire:key="modal-opt-{{ $curso['id'] }}"
                                     class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-all mb-1 last:mb-0 {{ $isSelected ? 'bg-white shadow-sm ring-1 ring-Alumco-blue/10' : 'hover:bg-white/60' }}">
                                    <div class="w-3 h-3 rounded-full shrink-0 shadow-inner {{ $curso['bg'] }}"></div>
                                    <span class="text-sm {{ $isSelected ? 'font-black text-Alumco-blue' : 'font-bold text-gray-600' }} truncate">
                                        {{ $curso['titulo'] }}
                                    </span>
                                    @if($isSelected)
                                        <div class="ml-auto bg-Alumco-blue rounded-full p-1">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 opacity-40">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Sin resultados</p>
                                </div>
                            @endforelse
                        </div>
                        @error('cursoId') <p class="mt-1 text-[10px] font-bold text-red-500 ml-1 uppercase tracking-tighter">{{ $message }}</p> @enderror
                    </div>

                    {{-- Period selector --}}
                    <div class="grid grid-cols-2 gap-4">
                        @if($modoVista === 'anual')
                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Semana Inicio</label>
                                <select wire:model.live="semanaInicioPlan"
                                        class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all appearance-none cursor-pointer">
                                    @foreach($semanasDelAnio as $sem)
                                        <option value="{{ $sem['numero'] }}">
                                            S{{ $sem['numero'] }} — {{ \Carbon\Carbon::parse($sem['inicio'])->isoFormat('D MMM') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Semana Fin</label>
                                <select wire:model.live="semanaFinPlan"
                                        class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all appearance-none cursor-pointer">
                                    @foreach($semanasDelAnio as $sem)
                                        <option value="{{ $sem['numero'] }}">
                                            S{{ $sem['numero'] }} — {{ \Carbon\Carbon::parse($sem['fin'])->isoFormat('D MMM') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Fecha Inicio</label>
                                <input type="date" wire:model="fechaInicioPlan"
                                       class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all cursor-pointer">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Fecha Fin</label>
                                <input type="date" wire:model="fechaFinPlan"
                                       class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all cursor-pointer">
                            </div>
                        @endif
                    </div>

                    {{-- Preview dates (Annual mode only) --}}
                    @if($modoVista === 'anual' && $fechaInicioPlan && $fechaFinPlan)
                        <div class="bg-blue-50/50 rounded-2xl p-3 flex items-center gap-3 border border-blue-100/50">
                            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-Alumco-blue/80">
                                {{ \Carbon\Carbon::parse($fechaInicioPlan)->locale('es')->isoFormat('D [de] MMMM') }}
                                <span class="mx-1 text-blue-300">→</span>
                                {{ \Carbon\Carbon::parse($fechaFinPlan)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                            </span>
                        </div>
                    @endif

                    {{-- Extra info grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Sede selector --}}
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Sede del Evento</label>
                            <div class="relative">
                                <select wire:model="sedeIdPlan"
                                        class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all appearance-none cursor-pointer">
                                    <option value="">Todas las sedes (Global)</option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Notas Internas</label>
                            <textarea wire:model="notas" rows="1"
                                      placeholder="Ej: Turno mañana, sala A..."
                                      class="w-full bg-gray-50 border-none rounded-2xl py-3 px-4 text-sm font-bold text-Alumco-blue placeholder:text-gray-400 focus:ring-4 focus:ring-Alumco-blue/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Modal footer --}}
                <div class="px-8 pb-8 pt-2 flex gap-3">
                    <button wire:click="cerrarModal"
                            class="flex-1 py-4 bg-gray-50 text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-100 transition-all">
                        Descartar
                    </button>
                    <button wire:click="guardarPlanificacion"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 scale-95"
                            class="flex-[2] py-4 bg-Alumco-blue text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-Alumco-blue/20 hover:shadow-xl hover:shadow-Alumco-blue/30 transition-all">
                        <span wire:loading.remove wire:target="guardarPlanificacion">
                            {{ $editandoId ? 'Actualizar Evento' : 'Confirmar Planificación' }}
                        </span>
                        <span wire:loading wire:target="guardarPlanificacion" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
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
    <template x-if="deleteModal.show">
        <div class="fixed inset-0 bg-black/50 z-[100] flex justify-center items-center backdrop-blur-sm"
             @click="deleteModal.show = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden mx-4"
                 @click.stop>
                <div class="bg-red-600 px-5 py-4 flex justify-between items-center text-white">
                    <h3 class="font-bold text-base">Confirmar eliminación</h3>
                    <button @click="deleteModal.show = false"
                            class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-700 mb-4" 
                       x-text="userSexo === 'F' ? '¿Estás segura de que quieres eliminar esta planificación?' : '¿Estás seguro de que quieres eliminar esta planificación?'"></p>
                    <div class="flex justify-end gap-2">
                        <button @click="deleteModal.show = false"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium text-sm">
                            Cancelar
                        </button>
                        <button @click="executeDelete()"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium text-sm">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('calendarManager', (config) => ({
            modoVista: config.modoVista,
            modoPlaneacion: config.modoPlaneacion,
            mostrarModalPlanificacion: config.mostrarModalPlanificacion,
            readonly: config.readonly,
            diasEnMes: config.diasEnMes,
            userSexo: config.userSexo,

            // State for Drag & Drop
            isDragging: false,
            isResizing: false,
            dragStartDay: null,
            dragEndDay: null,
            draggedEventId: null,
            draggedSedeId: null,
            origSedeId: null,
            resizeEdge: null,
            
            // Annual View Overhaul State
            sedesColapsadas: [],
            hoveredSemana: null,
            hoveredSedeId: null,
            
            // Delete modal state
            deleteModal: {
                show: false,
                id: null
            },
            
            // Tooltip state
            tooltip: {
                show: false,
                text: '',
                x: 0,
                y: 0
            },

            init() {
                // Listen for keydown events for navigation and escaping
                window.addEventListener('keydown', (e) => {
                    if (e.target.matches('input, textarea, select')) return;
                    
                    if (e.key === 'Escape') {
                        if (this.mostrarModalPlanificacion) {
                            this.mostrarModalPlanificacion = false;
                        } else if (this.modoPlaneacion) {
                            this.modoPlaneacion = false;
                        }
                    }
                    
                    if (this.modoVista === 'mensual') {
                        if (e.key === 'ArrowLeft') this.$wire.mesAnterior();
                        if (e.key === 'ArrowRight') this.$wire.mesSiguiente();
                    } else {
                        if (e.key === 'ArrowLeft') this.$wire.irAnioAnterior();
                        if (e.key === 'ArrowRight') this.$wire.irAnioSiguiente();
                    }
                });

                window.addEventListener('mousemove', (e) => {
                    if (this.modoVista === 'mensual') {
                        const cell = document.elementFromPoint(e.clientX, e.clientY)?.closest('[data-day]');
                        if (cell) {
                            this.updateMonthlyAction(parseInt(cell.dataset.day), e);
                        }
                    } else if (this.modoVista === 'anual') {
                        const cell = document.elementFromPoint(e.clientX, e.clientY)?.closest('.cal-annual-cell');
                        if (cell) {
                            this.updateAnnualAction(parseInt(cell.dataset.semana), parseInt(cell.dataset.sedeId || 0), e);
                        }
                    }
                });

                window.addEventListener('mouseup', () => {
                    if (this.modoVista === 'mensual') {
                        this.endMonthlyAction();
                    } else if (this.modoVista === 'anual') {
                        this.endAnnualAction();
                    }
                });
            },

            showTooltip(text, x, y) {
                this.tooltip.text = text;
                this.tooltip.x = x + 14;
                this.tooltip.y = y - 32;
                this.tooltip.show = true;
            },

            hideTooltip() {
                this.tooltip.show = false;
            },

            // Create Logic (Monthly)
            startDragCreate(day) {
                if (this.readonly || !this.modoPlaneacion) return;
                this.isDragging = true;
                this.dragStartDay = day;
                this.dragEndDay = day;
            },

            updateDragCreate(day) {
                if (!this.isDragging) return;
                this.dragEndDay = day;
            },

            endDragCreate() {
                if (!this.isDragging) return;
                
                const start = Math.min(this.dragStartDay, this.dragEndDay);
                const end = Math.max(this.dragStartDay, this.dragEndDay);
                
                if (start === end) {
                    this.$wire.abrirModalPlanificacion(start);
                } else {
                    this.$wire.abrirModalPlanificacionRango(start, end);
                }
                
                this.isDragging = false;
                this.dragStartDay = null;
                this.dragEndDay = null;
            },

            isDayHighlighted(day) {
                if (!this.isDragging || this.draggedEventId) return false;
                const start = Math.min(this.dragStartDay, this.dragEndDay);
                const end = Math.max(this.dragStartDay, this.dragEndDay);
                return day >= start && day <= end;
            },

            // Move/Resize Logic (Monthly)
            startMove(id, day, span, sedeNombre, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isDragging = true;
                this.draggedEventId = id;
                this.dragStartDay = day;
                this.dragEndDay = day;
                this.dragSpan = span;
                this.draggedSedeNombre = sedeNombre;
            },

            startResize(id, day, edge, sedeNombre, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isResizing = true;
                this.draggedEventId = id;
                this.dragStartDay = day;
                this.dragEndDay = day;
                this.resizeEdge = edge;
                this.draggedSedeNombre = sedeNombre;
            },

            updateMonthlyAction(day, event) {
                if (!this.isDragging && !this.isResizing) return;
                this.dragEndDay = day;
                
                const sedeLabel = this.draggedSedeNombre ? ` [${this.draggedSedeNombre}]` : '';
                
                if (this.isResizing) {
                    this.showTooltip(`${this.resizeEdge === 'inicio' ? 'Inicio: ' : 'Fin: '}${day}${sedeLabel}`, event.clientX, event.clientY);
                } else if (this.isDragging && this.draggedEventId) {
                    const end = Math.min(day + this.dragSpan - 1, this.diasEnMes);
                    this.showTooltip(`Mover: ${day} → ${end}${sedeLabel}`, event.clientX, event.clientY);
                }
            },

            endMonthlyAction() {
                if (this.isResizing) {
                    if (this.dragEndDay !== this.dragStartDay) {
                        this.$wire.ajustarBordePlanificacion(this.draggedEventId, this.resizeEdge, this.dragEndDay);
                    }
                } else if (this.isDragging && this.draggedEventId) {
                    if (this.dragEndDay !== this.dragStartDay) {
                        this.$wire.moverPlanificacion(this.draggedEventId, this.dragEndDay);
                    }
                } else {
                    this.endDragCreate();
                    return;
                }

                this.isDragging = false;
                this.isResizing = false;
                this.draggedEventId = null;
                this.dragStartDay = null;
                this.dragEndDay = null;
                this.hideTooltip();
            },

            // Annual View Logic
            startAnnualDragCreate(semana, sedeId) {
                if (this.readonly || !this.modoPlaneacion) return;
                this.isDragging = true;
                this.dragStartDay = semana;
                this.dragEndDay = semana;
                this.draggedSedeId = sedeId;
            },

            updateAnnualAction(semana, sedeId, event) {
                if (!this.isDragging && !this.isResizing) return;
                this.dragEndDay = semana;
                
                if (this.draggedEventId) {
                    // Move or Resize existing
                    if (this.isResizing) {
                        this.showTooltip((this.resizeEdge === 'inicio' ? 'Inicio: S' : 'Fin: S') + semana, event.clientX, event.clientY);
                    } else {
                        this.draggedSedeId = sedeId;
                        const end = semana + this.dragSpan;
                        this.showTooltip(`Mover: S${semana} → S${end}`, event.clientX, event.clientY);
                    }
                } else {
                    // Drag to create
                    if (this.draggedSedeId === sedeId) {
                        const start = Math.min(this.dragStartDay, this.dragEndDay);
                        const end = Math.max(this.dragStartDay, this.dragEndDay);
                        this.showTooltip(`Crear: S${start} → S${end}`, event.clientX, event.clientY);
                    }
                }
            },

            endAnnualAction() {
                if (!this.isDragging && !this.isResizing) return;

                if (this.draggedEventId) {
                    if (this.isResizing) {
                        if (this.dragEndDay !== this.dragStartDay) {
                            this.$wire.ajustarBordePlanificacionSemana(this.draggedEventId, this.resizeEdge, this.dragEndDay);
                        }
                    } else {
                        if (this.dragEndDay !== this.dragStartDay || this.draggedSedeId !== this.origSedeId) {
                            this.$wire.moverPlanificacionSemanas(this.draggedEventId, this.dragEndDay, this.draggedSedeId);
                        }
                    }
                } else {
                    // Drag to create
                    const start = Math.min(this.dragStartDay, this.dragEndDay);
                    const end = Math.max(this.dragStartDay, this.dragEndDay);
                    if (start === end) {
                        this.$wire.abrirModalAnualSemana(start, this.draggedSedeId);
                    } else {
                        this.$wire.abrirModalAnualRango(start, end, this.draggedSedeId);
                    }
                }

                this.isDragging = false;
                this.isResizing = false;
                this.draggedEventId = null;
                this.dragStartDay = null;
                this.dragEndDay = null;
                this.draggedSedeId = null;
                this.hideTooltip();
            },

            startAnnualMove(id, semana, span, sedeId, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isDragging = true;
                this.draggedEventId = id;
                this.dragStartDay = semana;
                this.dragEndDay = semana;
                this.dragSpan = span;
                this.draggedSedeId = sedeId;
                this.origSedeId = sedeId;
            },

            startAnnualResize(id, semana, edge, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isResizing = true;
                this.draggedEventId = id;
                this.dragStartDay = semana;
                this.dragEndDay = semana;
                this.resizeEdge = edge;
            },

            isAnnualCellHighlighted(semana, sedeId) {
                if (!this.isDragging || this.draggedEventId) return false;
                if (this.draggedSedeId !== sedeId) return false;
                const start = Math.min(this.dragStartDay, this.dragEndDay);
                const end = Math.max(this.dragStartDay, this.dragEndDay);
                return semana >= start && semana <= end;
            },
            
            isAnnualMoveTarget(semana, sedeId) {
                if (!this.isDragging || !this.draggedEventId || this.isResizing) return false;
                if (this.draggedSedeId !== sedeId) return false;
                const start = this.dragEndDay;
                const end = start + this.dragSpan;
                return semana >= start && semana <= end;
            },

            // Annual Overhaul Helpers
            toggleSede(sedeId) {
                if (this.sedesColapsadas.includes(sedeId)) {
                    this.sedesColapsadas = this.sedesColapsadas.filter(id => id !== sedeId);
                } else {
                    this.sedesColapsadas.push(sedeId);
                }
            },

            isSedeColapsada(sedeId) {
                return this.sedesColapsadas.includes(sedeId);
            },

            setHover(semana, sedeId) {
                this.hoveredSemana = semana;
                this.hoveredSedeId = sedeId;
            },

            clearHover() {
                this.hoveredSemana = null;
                this.hoveredSedeId = null;
            },

            isHovered(semana, sedeId) {
                if (this.isDragging || this.isResizing) return false;
                return this.hoveredSemana === semana || this.hoveredSedeId === sedeId;
            },

            // Confirmation modal helper
            confirmDelete(id) {
                this.deleteModal.id = id;
                this.deleteModal.show = true;
            },

            executeDelete() {
                if (this.deleteModal.id) {
                    this.$wire.borrarPlanificacion(this.deleteModal.id);
                    this.deleteModal.show = false;
                    this.deleteModal.id = null;
                }
            },

        }));
    });
</script>
@endpush

