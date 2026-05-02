@section('header_title', 'Calendario de Eventos')

@push('css')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

    .cal-bar { transition: transform 0.1s ease, box-shadow 0.1s ease, filter 0.1s ease; position: relative; }
    .cal-bar:hover { transform: translateY(-1px); box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.12); }
    
    .plan-mode-active .cal-day-cell:hover { background-color: rgba(32, 80, 153, 0.02); cursor: cell; }
    
    [x-cloak] { display: none !important; }

    /* Command Bar & Navigation */
    .command-bar { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(229, 231, 235, 1); }
    .nav-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .nav-btn:hover:not(:disabled) { transform: translateY(-1px); background-color: #f3f4f6; }
    .nav-btn:active:not(:disabled) { transform: translateY(0); }

    /* Plan Mode Theme */
    .plan-mode-active .command-bar { border-color: rgba(32, 80, 153, 0.3); ring: 4px; ring-color: rgba(32, 80, 153, 0.05); }
    .plan-mode-active .plan-toggle-btn { background-color: #205099; color: white; box-shadow: 0 4px 12px rgba(32, 80, 153, 0.2); }

    /* Modal & Slide-over animations */
    .slide-over-enter { transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .slide-over-enter-active { transform: translateX(0); }
    .slide-over-exit { transform: translateX(0); transition: transform 0.3s cubic-bezier(0.7, 0, 0.84, 0); }
    .slide-over-exit-active { transform: translateX(100%); }

    #planning-modal .transform { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .modal-backdrop { backdrop-filter: blur(4px); background: rgba(32, 80, 153, 0.1); }
    
    /* Quick-add popover */
    .quick-add-popover { animation: popIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes popIn { from { opacity: 0; transform: translate(-50%, -45%) scale(0.95); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }

    /* Grid Overhaul */
    .cal-grid-border { border-color: #f1f3f5; }
    .cal-sticky-col { box-shadow: 1px 0 0 0 #f1f3f5, 4px 0 12px -4px rgba(0,0,0,0.05); }

    /* Chip Refinement */
    .cal-event-chip { border: 1px solid rgba(0,0,0,0.05); }
    .cal-event-handle { opacity: 0; transition: opacity 0.15s ease; }
    .cal-event-chip:hover .cal-event-handle { opacity: 1; }
    
    /* + button on day cells */
    .cal-day-plus-btn { opacity: 0; transform: scale(0.9); transition: all 0.2s ease; }
    .cal-day-bg:hover .cal-day-plus-btn { opacity: 1; transform: scale(1); }
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
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-display font-black text-Alumco-blue tracking-tight">Planificación de Capacitaciones</h2>
            <p class="text-sm font-medium text-gray-500 mt-1">Gestiona el calendario institucional y la carga académica por sede.</p>
        </div>
        
        @if($esAdmin && $modoVista === 'anual')
            <button wire:click="toggleModoPlaneacion"
                    class="group flex items-center gap-2.5 px-5 py-2.5 rounded-2xl font-bold text-sm transition-all duration-300 plan-toggle-btn
                           {{ $modoPlaneacion
                               ? 'bg-Alumco-blue text-white shadow-lg shadow-Alumco-blue/20 ring-4 ring-Alumco-blue/10'
                               : 'bg-white text-Alumco-blue border border-gray-200 hover:border-Alumco-blue/30 hover:bg-gray-50 shadow-sm' }}">
                <div @class(['w-2 h-2 rounded-full', 'bg-white animate-pulse' => $modoPlaneacion, 'bg-Alumco-blue' => !$modoPlaneacion])></div>
                @if($modoPlaneacion)
                    <span>Salir de planificación</span>
                    <kbd class="ml-1 text-[10px] bg-white/20 px-1.5 py-0.5 rounded opacity-80">Esc</kbd>
                @else
                    <span>Activar Planificación</span>
                @endif
            </button>
        @endif
    </div>

    <div class="mb-4 command-bar p-2 rounded-2xl shadow-sm flex flex-col xl:flex-row items-stretch xl:items-center gap-2">

        {{-- Left: Date & Navigation --}}
        <div class="flex items-center gap-1 bg-gray-50/50 p-1 rounded-xl border border-gray-100/50">
            @if($modoVista === 'anual')
                <div class="flex items-center gap-0.5 mr-1">
                    <button wire:click="irAnioAnterior" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-black text-gray-700 min-w-[3.5rem] text-center">{{ $anioActual }}</span>
                    <button wire:click="irAnioSiguiente" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                
                <div class="h-6 w-px bg-gray-200 mx-1"></div>

                <div class="flex items-center gap-1">
                    <button wire:click="ventanaAnterior" @disabled($esPrimerVentana) class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn disabled:opacity-30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    
                    @php
                        $primerMesVentana = $mesesHeaderVentana[0]['mes'] ?? $mesActual;
                        $ultimoMesVentana = $mesesHeaderVentana[count($mesesHeaderVentana) - 1]['mes'] ?? $mesActual;
                        $rangoMesesVentana = $primerMesVentana === $ultimoMesVentana
                            ? $nombresMeses[$primerMesVentana - 1]
                            : $nombresMeses[$primerMesVentana - 1].' - '.$nombresMeses[$ultimoMesVentana - 1];
                    @endphp
                    <div class="px-2 text-center min-w-[9rem]">
                        <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">Mostrando</span>
                        <span class="block text-[11px] font-bold text-gray-700 leading-none">{{ $rangoMesesVentana }}</span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Semanas {{ $ventanaInicio }}-{{ $ventanaFin }}</span>
                    </div>

                    <button wire:click="ventanaSiguiente" @disabled($esUltimaVentana) class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn disabled:opacity-30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                @if(!$esVentanaActual)
                    <button wire:click="irVentanaHoy" class="ml-1 px-3 py-1.5 text-[11px] font-bold text-Alumco-blue hover:bg-Alumco-blue/5 rounded-lg transition-all">Hoy</button>
                @endif
            @else
                {{-- Mensual Navigation --}}
                <div class="flex items-center gap-1">
                    <button wire:click="mesAnterior" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-black text-gray-700 min-w-[8rem] text-center">
                        {{ ucfirst(\Carbon\Carbon::create()->month($mesActual)->locale('es')->translatedFormat('F')) }} {{ $anioActual }}
                    </span>
                    <button wire:click="mesSiguiente" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-Alumco-blue hover:bg-white rounded-lg transition-all nav-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @if(!$esMesActual)
                        <button wire:click="irAHoy" class="ml-1 px-3 py-1.5 text-[11px] font-bold text-Alumco-blue hover:bg-Alumco-blue/5 rounded-lg transition-all">Hoy</button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Center: Filters --}}
        <div class="flex-1 flex items-center gap-2 justify-center px-4">
            <div class="relative group" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center gap-2.5 px-4 py-2 bg-gray-50/50 border border-gray-100 hover:border-gray-200 rounded-xl transition-all group">
                    <div class="w-1.5 h-1.5 rounded-full {{ empty($filtroSedesIds) ? 'bg-gray-300' : 'bg-Alumco-blue' }}"></div>
                    <span class="text-xs font-bold text-gray-600">
                        @if(empty($filtroSedesIds)) Todas las sedes @elseif(count($filtroSedesIds) === 1) {{ collect($sedes)->firstWhere('id', $filtroSedesIds[0])['nombre'] }} @else {{ count($filtroSedesIds) }} sedes @endif
                    </span>
                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform group-hover:text-gray-600" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                {{-- Dropdown list (re-styled) --}}
                <div x-show="open" @click.away="open = false" x-transition class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 bg-white border border-gray-100 rounded-2xl shadow-xl z-[60] py-2">
                    <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filtrar por Sede</span>
                        @if(!empty($filtroSedesIds))
                            <button wire:click="$set('filtroSedesIds', [])" class="text-[10px] font-bold text-red-500 hover:text-red-600">Limpiar</button>
                        @endif
                    </div>
                    <div class="max-h-60 overflow-y-auto custom-scrollbar">
                        @foreach($sedes as $s)
                            <button wire:click="filtrarPorSede({{ $s['id'] }})" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs transition-colors hover:bg-gray-50 {{ in_array($s['id'], $filtroSedesIds) ? 'text-Alumco-blue font-bold' : 'text-gray-600' }}">
                                <div @class(['w-4 h-4 rounded-md border flex items-center justify-center', 'bg-Alumco-blue border-Alumco-blue' => in_array($s['id'], $filtroSedesIds), 'bg-white border-gray-200' => !in_array($s['id'], $filtroSedesIds)])>
                                    @if(in_array($s['id'], $filtroSedesIds)) <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg> @endif
                                </div>
                                {{ $s['nombre'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($modoVista === 'anual')
                <div class="h-6 w-px bg-gray-200"></div>
                <label class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ir a mes:</span>
                    <select wire:change="saltarAMes($event.target.value)" 
                            class="bg-transparent text-xs font-bold text-gray-500 outline-none cursor-pointer hover:text-Alumco-blue transition-colors">
                        @foreach($nombresMeses as $mIdx => $mNombre)
                            <option value="{{ $mIdx + 1 }}" @selected($primerMesVentana === ($mIdx + 1))>{{ $mNombre }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>

        {{-- Right: View Toggle & Global Actions --}}
        <div class="flex items-center gap-2">
            <div class="flex items-center p-1 bg-gray-100/80 rounded-xl">
                <button wire:click="cambiarVista('anual')" @class(['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', $modoVista === 'anual' ? 'bg-white text-Alumco-blue shadow-sm' : 'text-gray-500 hover:text-gray-700'])>Anual</button>
                <button wire:click="cambiarVista('mensual')" @class(['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', $modoVista === 'mensual' ? 'bg-white text-Alumco-blue shadow-sm' : 'text-gray-500 hover:text-gray-700'])>Mensual</button>
            </div>

            @if($esAdmin && $modoVista === 'anual')
                <button wire:click="abrirModalCopiarAnio" class="p-2.5 text-gray-400 hover:text-Alumco-blue hover:bg-Alumco-blue/5 rounded-xl transition-all border border-transparent hover:border-Alumco-blue/10" title="Copiar planificación a otro año">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2"/></svg>
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
                        foreach ($semanasVisibles as $sv) {
                            $sd = $fila['semanas'][$sv['numero']] ?? null;
                            if ($sd && $sd['conflicto']) {
                                $todosConflictos[] = [
                                    'sede_nombre' => $fila['nombre'],
                                    'numero'      => $sv['numero'],
                                    'inicio'      => $sv['inicio'],
                                    'fin'         => $sv['fin'],
                                    'cursos'      => collect($sd['cursos'])->unique('id')->values()->all(),
                                ];
                            }
                        }
                    }
                @endphp
                @if($esAdmin && count($todosConflictos) > 0)
                    <div class="mb-4 rounded-2xl border border-amber-100 bg-amber-50/50 overflow-hidden shadow-sm shadow-amber-900/5">
                        <div class="flex items-center gap-2.5 px-4 py-3 bg-amber-50 border-b border-amber-100">
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-amber-800 uppercase tracking-widest leading-none">Solapamientos Detectados</h4>
                                <p class="text-[10px] font-bold text-amber-600 mt-1 uppercase">{{ count($todosConflictos) }} periodo{{ count($todosConflictos) > 1 ? 's' : '' }} con cruce de horarios</p>
                            </div>
                        </div>
                        <div class="px-4 py-3 max-h-40 overflow-y-auto custom-scrollbar space-y-2">
                            @foreach($todosConflictos as $conf)
                                <div class="flex items-center gap-3 text-[11px]">
                                    <span class="font-black text-amber-800 bg-amber-100/50 px-2 py-0.5 rounded shrink-0">S{{ $conf['numero'] }}</span>
                                    <span class="text-amber-700 font-bold shrink-0">{{ $conf['sede_nombre'] }}</span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($conf['cursos'] as $c)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-white text-[9px] font-black uppercase tracking-tight {{ $c['bg'] }}">
                                                {{ $c['titulo'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── 16-week sliding window grid ────────────────────────── --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" @mouseup.window="endAnnualAction()">

                    <div class="grid" style="grid-template-columns: 160px repeat({{ count($semanasVisibles) }}, minmax(0, 1fr))">

                        {{-- Row 1: Month header spans ──────────────────────── --}}
                        <div class="bg-gray-50/50 border-r border-b border-gray-100 p-2 cal-sticky-col z-40 sticky left-0"></div>
                        @foreach($mesesHeaderVentana as $mh)
                            @php $esMesActualMh = $mh['mes'] === now()->month && $anioActual === now()->year; @endphp
                            <div @class([
                                     'p-2 text-center text-[10px] font-black uppercase tracking-widest border-b border-r border-gray-100 last:border-r-0 select-none transition-colors',
                                     'bg-Alumco-blue/5 text-Alumco-blue' => $esMesActualMh,
                                     'bg-gray-50/50 text-gray-400' => !$esMesActualMh,
                                 ])
                                 style="grid-column: span {{ $mh['span'] }}">
                                {{ $nombresMeses[$mh['mes'] - 1] }}
                            </div>
                        @endforeach

                        {{-- Row 2: Week numbers ─────────────────────────────── --}}
                        <div class="sticky left-0 z-40 bg-gray-50/80 backdrop-blur-sm border-r border-b-2 border-gray-100 p-2 cal-sticky-col">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Sede / Semanas</span>
                        </div>
                        @foreach($semanasVisibles as $sv)
                            <div @class([
                                     'text-center text-[11px] py-2 border-r border-b-2 border-gray-100 select-none font-bold',
                                     'bg-Alumco-blue/10 text-Alumco-blue font-black' => $sv['esHoy'],
                                     'text-gray-300' => $sv['esPasada'] && !$sv['esHoy'],
                                     'text-gray-500' => !$sv['esPasada'] && !$sv['esHoy'],
                                 ])>
                                {{ $sv['numero'] }}
                            </div>
                        @endforeach

                        {{-- Data rows ────────────────────────────────────────── --}}
                        @foreach($filasAnuales as $fila)
                            @if($readonly && $fila['sede_id'] !== null && $fila['sede_id'] !== $userSedeId)
                                @continue
                            @endif

                            @php
                                $sedeKey = $fila['sede_id'] ?? 0;
                                $sedePaletteColors = ['bg-Alumco-blue', 'bg-amber-500', 'bg-emerald-500', 'bg-rose-500', 'bg-violet-500'];
                                $sedeColorIdx = $fila['sede_id'] === null ? 0 : ($fila['sede_id'] % (count($sedePaletteColors) - 1)) + 1;
                                $sedeBg = $sedePaletteColors[$sedeColorIdx];
                            @endphp

                            {{-- Row label --}}
                            <div class="sticky left-0 z-30 bg-white border-r border-b border-gray-50 p-3 flex items-center gap-2.5 min-h-[60px] select-none cal-sticky-col">
                                <div class="w-1.5 h-1.5 rounded-full shrink-0 {{ $sedeBg }} shadow-sm"></div>
                                <span class="truncate text-[10px] font-black uppercase tracking-tight text-gray-600 leading-tight">{{ $fila['nombre'] }}</span>
                            </div>

                            {{-- Week cells --}}
                            @foreach($semanasVisibles as $sv)
                                @php
                                    $sd = $fila['semanas'][$sv['numero']] ?? ['cursos' => [], 'conflicto' => false];
                                @endphp
                                <div @class([
                                         'border-r border-b border-gray-50 last:border-r-0 relative min-h-[60px] flex flex-col gap-1 pt-1.5 pb-1 px-1 transition-colors',
                                         'bg-Alumco-blue/[0.02]' => $sv['esHoy'],
                                         'bg-gray-50/20' => $sv['esPasada'] && !$sv['esHoy'],
                                     ])
                                     wire:key="cell-anual-{{ $sedeKey }}-{{ $sv['numero'] }}"
                                     :class="isAnnualCellHighlighted({{ $sv['numero'] }}, {{ $sedeKey }}) ? 'bg-Alumco-blue/10' : (isAnnualMoveTarget({{ $sv['numero'] }}, {{ $sedeKey }}) ? 'bg-emerald-50' : '')"
                                     @if($esAdmin && $modoPlaneacion)
                                         @mousedown.self.prevent="startAnnualDragCreate({{ $sv['numero'] }}, {{ $sedeKey }})"
                                         @mousemove.stop="updateAnnualAction({{ $sv['numero'] }}, {{ $sedeKey }}, $event)"
                                     @endif>

                                    {{-- Course chips --}}
                                    @foreach($sd['cursos'] as $cIdx => $curso)
                                        <div @class([
                                                 'cal-bar cal-event-chip flex items-center h-7 overflow-hidden cursor-pointer shadow-sm ring-1 ring-black/5 group/event',
                                                 $curso['bg'],
                                                 'rounded-l-xl' => $curso['esInicio'] && !$curso['esFin'],
                                                 'rounded-r-xl' => !$curso['esInicio'] && $curso['esFin'],
                                                 'rounded-xl'   => $curso['esInicio'] && $curso['esFin'],
                                             ])
                                             :class="draggedEventId === {{ $curso['id'] }} ? 'opacity-40 scale-[0.98]' : ''"
                                             wire:key="chip-anual-{{ $sedeKey }}-{{ $sv['numero'] }}-{{ $cIdx }}"
                                             @click.stop="if(!isDragging && !isResizing) $wire.editarPlanificacion({{ $curso['id'] }})">

                                            {{-- Left resize handle --}}
                                            @if($esAdmin && $modoPlaneacion && $curso['esInicio'])
                                                <div class="w-1.5 h-full bg-white/30 hover:bg-white/50 cursor-ew-resize shrink-0 transition-colors"
                                                     @mousedown.stop.prevent="startAnnualResize({{ $curso['id'] }}, {{ $curso['semaInicio'] }}, 'inicio', $event)"></div>
                                            @endif

                                            {{-- Move zone + title + handle --}}
                                            @if($esAdmin && $modoPlaneacion)
                                                <div class="flex-1 min-w-0 flex items-center h-full px-1.5 cursor-grab active:cursor-grabbing"
                                                     @mousedown.stop.prevent="startAnnualMove({{ $curso['id'] }}, {{ $sv['numero'] }}, {{ $curso['semaFin'] - $curso['semaInicio'] }}, {{ $sedeKey }}, $event)">
                                                    
                                                    @if($curso['esInicio'])
                                                        {{-- Drag Handle --}}
                                                        <div class="cal-event-handle mr-1.5 text-white/60">
                                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 7h2v2H7V7zm0 4h2v2H7v-2zm4-4h2v2h-2V7zm0 4h2v2h-2v-2z"/></svg>
                                                        </div>
                                                        <span class="text-white text-[10px] font-black uppercase tracking-tight truncate leading-none drop-shadow-sm">
                                                            {{ $curso['titulo'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($curso['esInicio'])
                                                <span class="flex-1 px-2 text-white text-[10px] font-black uppercase tracking-tight truncate leading-none drop-shadow-sm">
                                                    {{ $curso['titulo'] }}
                                                </span>
                                            @endif

                                            {{-- Delete button (hover) --}}
                                            @if($esAdmin && $curso['esFin'])
                                                <button type="button"
                                                        @click.stop="confirmDelete({{ $curso['id'] }})"
                                                        class="opacity-0 group-hover/event:opacity-100 w-6 h-full flex items-center justify-center bg-black/10 hover:bg-red-500 text-white transition-all duration-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif

                                            {{-- Right resize handle --}}
                                            @if($esAdmin && $modoPlaneacion && $curso['esFin'])
                                                <div class="w-1.5 h-full bg-white/30 hover:bg-white/50 cursor-ew-resize shrink-0 transition-colors"
                                                     @mousedown.stop.prevent="startAnnualResize({{ $curso['id'] }}, {{ $curso['semaFin'] }}, 'fin', $event)"></div>
                                            @endif
                                        </div>
                                    @endforeach

                                    {{-- Conflict indicator --}}
                                    @if(!$readonly && $sd['conflicto'])
                                        <div class="absolute top-0.5 right-0.5 w-4 h-4 bg-amber-500 text-white rounded-full flex items-center justify-center text-[10px] font-black shadow-sm ring-2 ring-white z-20 cursor-help group/conflict"
                                             title="Solapamiento de eventos">
                                            !
                                            <div class="hidden group-hover/conflict:block absolute bottom-full right-0 mb-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 p-2 z-[60]">
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 border-b border-gray-50 pb-1">Cursos en conflicto:</p>
                                                <div class="space-y-1">
                                                    @foreach($sd['cursos_popover'] ?? $sd['cursos'] as $c)
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
                                         'cal-day-bg relative border-r border-gray-100 last:border-0 transition-colors',
                                         'bg-Alumco-blue/5' => $diaInfo['esHoy'],
                                         'opacity-40'       => ! $diaInfo['esMesActual'],
                                         'bg-gray-50/50'    => $diaInfo['esWeekend'] && $diaInfo['esMesActual'],
                                     ])
                                     :class="isDayHighlighted({{ $diaInfo['num'] }}) ? 'bg-Alumco-blue/10' : ''"
                                     style="grid-column: {{ $dIdx + 1 }}; grid-row: 1 / -1;"
                                     @if($diaInfo['esMesActual'])
                                         data-day="{{ $diaInfo['num'] }}"
                                         @mousedown="handleMonthlyBgMousedown({{ $diaInfo['num'] }}, '{{ $diaInfo['fecha'] }}', $event)"
                                         @mouseover="updateDragCreate({{ $diaInfo['num'] }})"
                                         @mouseup="endDragCreate()"
                                     @endif>
                                    @if($esAdmin && $diaInfo['esMesActual'])
                                        <button type="button"
                                                class="cal-day-plus-btn absolute bottom-1 right-1 w-5 h-5 rounded-full bg-Alumco-blue/10 hover:bg-Alumco-blue/20 text-Alumco-blue text-xs font-black flex items-center justify-center z-10 leading-none"
                                                @click.stop="$wire.abrirQuickAdd('{{ $diaInfo['fecha'] }}')"
                                                title="Agregar planificación">+</button>
                                    @endif
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

    {{-- ── Quick-add popover ──────────────────────────────────────────────── --}}
    @if($mostrarQuickAdd)
        <div class="fixed inset-0 z-40 bg-black/5 backdrop-blur-[1px]" wire:click="$set('mostrarQuickAdd', false)"></div>
        <div class="quick-add-popover fixed z-50 bg-white rounded-3xl shadow-2xl border border-gray-100 w-80 overflow-hidden"
             style="top: 50%; left: 50%; transform: translate(-50%, -50%)"
             wire:click.stop>

            <div class="px-6 pt-6 pb-4 flex items-center justify-between border-b border-gray-50 bg-gray-50/30">
                <div>
                    <h4 class="text-[10px] font-black text-Alumco-blue/40 uppercase tracking-widest">Planificación Rápida</h4>
                    <p class="text-sm font-black text-Alumco-blue mt-0.5">
                        {{ \Carbon\Carbon::parse($quickAddFecha)->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                    </p>
                </div>
                <button wire:click="$set('mostrarQuickAdd', false)"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-gray-400 hover:text-red-500 hover:shadow-sm transition-all border border-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Course search --}}
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Seleccionar Curso</label>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.200ms="queryModal"
                               placeholder="Buscar capacitación..."
                               class="w-full bg-gray-50 border border-gray-100 rounded-xl py-2.5 pl-9 pr-3 text-xs font-bold text-Alumco-blue placeholder:text-gray-400 focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue/20 transition-all"
                               autocomplete="off">
                        <svg class="absolute left-3 top-3 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    </div>

                    <div class="bg-gray-50/50 rounded-xl p-1 max-h-40 overflow-y-auto custom-scrollbar border border-gray-100/50 mt-2">
                        @forelse($modalList as $curso)
                            @php $isSelected = ($cursoId == $curso['id']); @endphp
                            <div wire:click="seleccionarCurso({{ $curso['id'] }})"
                                 wire:key="qa-opt-{{ $curso['id'] }}"
                                 class="flex items-center gap-2.5 px-3 py-2 rounded-lg cursor-pointer transition-all {{ $isSelected ? 'bg-white shadow-sm ring-1 ring-Alumco-blue/10' : 'hover:bg-white/60' }}">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0 {{ $curso['bg'] }} shadow-sm"></div>
                                <span class="text-xs {{ $isSelected ? 'font-black text-Alumco-blue' : 'font-bold text-gray-500' }} truncate">{{ $curso['titulo'] }}</span>
                                @if($isSelected)
                                    <div class="ml-auto bg-Alumco-blue rounded-full p-0.5 shadow-sm">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center py-4 text-[10px] text-gray-400 font-black uppercase tracking-widest">Sin resultados</p>
                        @endforelse
                    </div>
                </div>

                {{-- Sede selection --}}
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Sede</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" wire:click="$set('sedeIdPlan', null)"
                                @class(['px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all border shadow-sm',
                                        'bg-Alumco-blue text-white border-Alumco-blue' => $sedeIdPlan === null,
                                        'bg-white text-gray-500 border-gray-100 hover:border-gray-200' => $sedeIdPlan !== null])>
                            Todas
                        </button>
                        @foreach($sedes as $sede)
                            <button type="button" wire:click="$set('sedeIdPlan', {{ $sede['id'] }})"
                                    @class(['px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all border shadow-sm',
                                            'bg-Alumco-blue text-white border-Alumco-blue' => (int)$sedeIdPlan === $sede['id'],
                                            'bg-white text-gray-500 border-gray-100 hover:border-gray-200' => (int)$sedeIdPlan !== $sede['id']])>
                                {{ $sede['nombre'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-5 pb-5 pt-2 flex gap-2">
                <button wire:click="guardarQuickAdd"
                        wire:loading.attr="disabled"
                        class="flex-1 py-3 bg-Alumco-blue text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-Alumco-blue/20 hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0">
                    <span wire:loading.remove wire:target="guardarQuickAdd">Confirmar</span>
                    <span wire:loading wire:target="guardarQuickAdd">Procesando...</span>
                </button>
                <button wire:click="escalarQuickAddAModal"
                        class="px-4 py-3 bg-gray-50 text-gray-500 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-100 transition-all border border-gray-100">
                    Opciones
                </button>
            </div>
        </div>
    @endif

    {{-- ── Planning Slide-Over ────────────────────────────────────────── --}}
    @if($mostrarModalPlanificacion)
        <div id="planning-modal" class="fixed inset-0 z-50 flex justify-end overflow-hidden" wire:click="cerrarModal">
            {{-- Backdrop --}}
            <div class="absolute inset-0 modal-backdrop transition-opacity duration-500" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"></div>
            
            {{-- Slide Panel --}}
            <div class="relative w-screen max-w-md bg-white shadow-2xl flex flex-col transform transition-transform duration-500 slide-over-enter"
                 x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0"
                 onclick="event.stopPropagation()">
                
                {{-- Header --}}
                <div class="px-8 pt-10 pb-6 border-b border-gray-50 flex items-center justify-between bg-Alumco-blue/5">
                    <div>
                        <h3 class="font-display font-black text-2xl text-Alumco-blue tracking-tight">
                            {{ $editandoId ? 'Editar Periodo' : 'Nueva Planificación' }}
                        </h3>
                        <p class="text-[10px] font-black text-Alumco-blue/40 uppercase tracking-widest mt-1">
                            {{ $editandoId ? 'Actualiza los detalles' : 'Programa una nueva cápsula' }}
                        </p>
                    </div>
                    <button wire:click="cerrarModal" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white text-gray-400 hover:text-red-500 hover:shadow-md border border-gray-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Content --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar px-8 py-8 space-y-8">
                    
                    {{-- Sede Selector --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Sede del Evento</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="$set('sedeIdPlan', null)"
                                    @class(['flex items-center gap-2 px-3 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border',
                                            'bg-Alumco-blue text-white border-Alumco-blue shadow-lg shadow-Alumco-blue/20' => $sedeIdPlan === null,
                                            'bg-white text-gray-500 border-gray-100 hover:border-gray-200' => $sedeIdPlan !== null])>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                Todas
                            </button>
                            @foreach($sedes as $sede)
                                <button type="button" wire:click="$set('sedeIdPlan', {{ $sede['id'] }})"
                                        @class(['flex items-center gap-2 px-3 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest transition-all border',
                                                'bg-Alumco-blue text-white border-Alumco-blue shadow-lg shadow-Alumco-blue/20' => (int)$sedeIdPlan === $sede['id'],
                                                'bg-white text-gray-400 border-gray-100 hover:border-gray-200' => (int)$sedeIdPlan !== $sede['id']])>
                                    <div @class(['w-1.5 h-1.5 rounded-full', 'bg-white' => (int)$sedeIdPlan === $sede['id'], 'bg-gray-300' => (int)$sedeIdPlan !== $sede['id']])></div>
                                    {{ $sede['nombre'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Course Search --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Seleccionar Capacitación</label>
                        <div class="relative group">
                            <input type="text" wire:model.live.debounce.200ms="queryModal" placeholder="Buscar..."
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue/20 transition-all">
                            <svg class="absolute left-4 top-4.5 w-4 h-4 text-gray-400 group-focus-within:text-Alumco-blue transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                        </div>
                        <div class="bg-gray-50/50 rounded-2xl p-2 max-h-60 overflow-y-auto custom-scrollbar border border-gray-100/50">
                            @forelse($modalList as $curso)
                                @php $isSelected = ($cursoId == $curso['id']); @endphp
                                <div wire:click="seleccionarCurso({{ $curso['id'] }})" wire:key="modal-opt-{{ $curso['id'] }}"
                                     class="flex items-center gap-3 px-4 py-3 rounded-2xl cursor-pointer transition-all mb-1.5 last:mb-0 {{ $isSelected ? 'bg-white shadow-md ring-1 ring-Alumco-blue/10' : 'hover:bg-white/60' }}">
                                    <div class="w-3.5 h-3.5 rounded-full shrink-0 shadow-inner {{ $curso['bg'] }}"></div>
                                    <span class="text-sm {{ $isSelected ? 'font-black text-Alumco-blue' : 'font-bold text-gray-500' }} truncate">{{ $curso['titulo'] }}</span>
                                    @if($isSelected)
                                        <div class="ml-auto bg-Alumco-blue rounded-full p-1 shadow-sm"><svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                    @endif
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 opacity-30">
                                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                                    <p class="text-[10px] font-black uppercase tracking-widest">Sin resultados</p>
                                </div>
                            @endforelse
                        </div>
                        @error('cursoId') <p class="text-[10px] font-black text-red-500 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Inicio</label>
                            <input type="date" wire:model.live="fechaInicioPlan" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 text-xs font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Fin</label>
                            <input type="date" wire:model.live="fechaFinPlan" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-3.5 px-4 text-xs font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Observaciones</label>
                        <textarea wire:model="notas" rows="3" placeholder="Notas adicionales..." class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 text-sm font-bold text-Alumco-blue focus:ring-4 focus:ring-Alumco-blue/10 transition-all resize-none"></textarea>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="px-8 py-8 border-t border-gray-50 bg-gray-50/20 flex gap-3">
                    <button wire:click="cerrarModal" class="flex-1 py-4 bg-white text-gray-400 border border-gray-100 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-50 transition-all">Cancelar</button>
                    <button wire:click="guardarPlanificacion" wire:loading.attr="disabled"
                            class="flex-[2] py-4 bg-Alumco-blue text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-Alumco-blue/20 hover:shadow-2xl hover:-translate-y-0.5 transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="guardarPlanificacion">{{ $editandoId ? 'Guardar Cambios' : 'Confirmar Planificación' }}</span>
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
                            this.$wire.cerrarModal();
                        } else if (this.modoPlaneacion) {
                            this.modoPlaneacion = false;
                        }
                        this.$wire.$set('mostrarQuickAdd', false);
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
                        // Use month-level action handler for the new annual grid
                        this.endAnnualMesAction();
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

            // Monthly background cell: quick-add when not in plan mode, drag-create when in plan mode
            handleMonthlyBgMousedown(day, fecha, event) {
                if (this.readonly) return;
                if (this.modoPlaneacion) {
                    this.startDragCreate(day);
                } else if (event.target.closest('.cal-day-plus-btn')) {
                    // + button click handled by its own @click.stop
                } else {
                    // plain click on empty area — do nothing here; quickAdd opens on mouseup via the + btn
                }
            },

            // Annual month-grid move (planning mode)
            startAnnualMoveMes(id, mes, sedeId, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isDragging = true;
                this.draggedEventId = id;
                this.dragStartDay = mes;
                this.dragEndDay = mes;
                this.dragSpan = 0;
                this.draggedSedeId = sedeId;
                this.origSedeId = sedeId;
            },

            // Annual month-grid resize (planning mode)
            startAnnualResizeMes(id, mes, edge, event) {
                if (this.readonly || !this.modoPlaneacion) return;
                event.stopPropagation();
                this.isResizing = true;
                this.draggedEventId = id;
                this.dragStartDay = mes;
                this.dragEndDay = mes;
                this.resizeEdge = edge;
            },

            // Annual month-grid: end drag/resize — calls month-level server methods
            endAnnualMesAction() {
                if (!this.isDragging && !this.isResizing) return;
                if (this.draggedEventId) {
                    if (this.isResizing) {
                        if (this.dragEndDay !== this.dragStartDay) {
                            this.$wire.ajustarBordePlanificacionMes(this.draggedEventId, this.resizeEdge, this.dragEndDay);
                        }
                    } else {
                        if (this.dragEndDay !== this.dragStartDay || this.draggedSedeId !== this.origSedeId) {
                            this.$wire.moverPlanificacionMes(this.draggedEventId, this.dragEndDay, this.draggedSedeId);
                        }
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
