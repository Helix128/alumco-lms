@extends('layouts.panel')

@section('title', 'Reportes de Capacitación')

@section('header_title', 'Reportes e Impacto')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h2 class="admin-page-title">Análisis de Cumplimiento</h2>
            <p class="admin-page-subtitle">Visualización de progreso y resultados por estamentos</p>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="mb-8">
    <form action="{{ route('admin.reportes.index') }}" method="GET" class="filter-card admin-surface p-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Sedes (Multi) --}}
            <div class="lg:col-span-3 space-y-2">
                <label class="admin-page-eyebrow">Sedes</label>
                <x-picker-multi name="sede_id" :options="$sedes->pluck('nombre', 'id')->toArray()" :selected="$selectedSedes" placeholder="Todas las sedes" />
            </div>

            {{-- Estamentos (Multi) --}}
            <div class="lg:col-span-3 space-y-2">
                <label class="admin-page-eyebrow">Estamentos</label>
                <x-picker-multi name="estamento_id" :options="$estamentos->pluck('nombre', 'id')->toArray()" :selected="$selectedEstamentos" placeholder="Todos los estamentos" />
            </div>

            {{-- Cursos (Multi) --}}
            <div class="lg:col-span-4 space-y-2">
                <label class="admin-page-eyebrow">Cursos Aprobados</label>
                <x-picker-multi name="curso_id" :options="$cursos->pluck('titulo', 'id')->toArray()" :selected="$selectedCursos" placeholder="Cualquier curso" />
            </div>

            {{-- Rango Etario --}}
            <div class="lg:col-span-2 space-y-2" id="age-filter-root">
                <label class="admin-page-eyebrow">Rango Etario</label>
                
                <div class="pt-2 px-1" id="age-slider-wrapper">
                    <div class="relative h-1 bg-gray-100 rounded-full">
                        <div id="age-range-fill" class="absolute h-full bg-Alumco-blue rounded-full" style="left: 0%; width: 100%;"></div>
                        <input type="range" id="age-min-slider" min="{{ $ageBounds['min'] }}" max="{{ $ageBounds['max'] }}" value="{{ request('edad_min', $ageBounds['min']) }}" class="absolute w-full -top-1.5 h-4 appearance-none bg-transparent pointer-events-none custom-slider">
                        <input type="range" id="age-max-slider" min="{{ $ageBounds['min'] }}" max="{{ $ageBounds['max'] }}" value="{{ request('edad_max', $ageBounds['max']) }}" class="absolute w-full -top-1.5 h-4 appearance-none bg-transparent pointer-events-none custom-slider">
                    </div>
                    <div class="flex justify-between mt-3 text-xs font-black text-Alumco-blue/60">
                        <span id="age-min-value">{{ request('edad_min', $ageBounds['min']) }}</span>
                        <span id="age-max-value">{{ request('edad_max', $ageBounds['max']) }}</span>
                    </div>
                </div>
                <input type="hidden" name="edad_min" id="edad-min-input" value="{{ request('edad_min', $ageBounds['min']) }}">
                <input type="hidden" name="edad_max" id="edad-max-input" value="{{ request('edad_max', $ageBounds['max']) }}">
            </div>

            <!-- Footer del Formulario -->
            <div class="lg:col-span-12 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    @php
                        $activeFiltersCount = 0;
                        if (count($selectedSedes) > 0) { $activeFiltersCount++; }
                        if (count($selectedEstamentos) > 0) { $activeFiltersCount++; }
                        if (count($selectedCursos) > 0) { $activeFiltersCount++; }
                        if ((request()->filled('edad_min') && request('edad_min') != $ageBounds['min']) || 
                            (request()->filled('edad_max') && request('edad_max') != $ageBounds['max'])) { 
                            $activeFiltersCount++; 
                        }
                        if (request()->filled('fecha_inicio') || request()->filled('fecha_fin')) { $activeFiltersCount++; }
                    @endphp
                    <span class="badge-filter">{{ $activeFiltersCount }} filtros aplicados</span>
                    @if($activeFiltersCount > 0)
                        <a href="{{ route('admin.reportes.index') }}" class="text-xs font-bold text-gray-400 hover:text-Alumco-coral transition-colors underline underline-offset-4">Limpiar todo</a>
                    @endif
                </div>

                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <button type="button" onclick="openExportModal()"
                        class="admin-action-button admin-action-button--success shadow-lg shadow-Alumco-green/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar Excel
                    </button>
                    
                    <button type="submit" class="flex-1 sm:flex-none admin-action-button admin-action-button--primary px-10 shadow-lg shadow-Alumco-blue/20">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="mb-4 flex items-center gap-3">
    <div class="h-px bg-gray-200 flex-1"></div>
    <span class="px-4 py-1 rounded-full bg-white border border-gray-100 text-[11px] font-display font-black text-Alumco-blue uppercase tracking-widest shadow-sm">
        {{ $usuarios->total() }} registros encontrados
    </span>
    <div class="h-px bg-gray-200 flex-1"></div>
</div>

<!-- Tabla Card-Style -->
<div class="admin-surface overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Colaborador</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Email</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">RUT</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Sexo</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Edad</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Estamento</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Sede</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Cursos</th>
                    @if($cursoSeleccionado)
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Aprobación</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($usuarios as $user)
                <tr class="hover:bg-Alumco-cream/30 transition-colors group cursor-default">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-bold text-xs shrink-0">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                            </div>
                            <p class="font-display font-bold text-Alumco-gray leading-tight text-sm">{{ $user->name }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm text-Alumco-gray font-medium">{{ $user->email }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-xs font-bold text-Alumco-blue/60 uppercase tracking-tight">{{ $user->rut ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray capitalize">{{ $user->sexo === 'F' ? 'Femenino' : ($user->sexo === 'M' ? 'Masculino' : 'Otro') }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray/60">{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->age . ' años' : '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray">{{ $user->estamento->nombre ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-[11px] font-black text-Alumco-blue/40 uppercase tracking-tighter">{{ $user->sede->nombre ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        @if($user->certificados->isEmpty())
                            <span class="text-xs text-gray-300 italic font-medium">Ninguno</span>
                        @else
                            <div x-data="{ open: false, x: 0, y: 0 }" 
                                 @mouseenter="const rect = $el.getBoundingClientRect(); x = rect.left + (rect.width / 2); y = rect.top; open = true" 
                                 @mouseleave="open = false" 
                                 class="relative cursor-help inline-block">
                                <span class="text-sm font-black text-Alumco-blue underline underline-offset-4 decoration-Alumco-blue/30 decoration-dotted">
                                    {{ $user->certificados->count() }} {{ Str::plural('curso', $user->certificados->count()) }}
                                </span>
                                
                                {{-- Tooltip con lista de cursos (Teleportado para evitar clipping) --}}
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         class="fixed z-[9999] w-80 bg-white border border-gray-100 text-Alumco-gray rounded-2xl p-5 shadow-[0_20px_50px_rgba(32,80,153,0.15)] pointer-events-none"
                                         :style="`left: ${x}px; top: ${y}px; transform: translate(-50%, calc(-100% - 10px))`">
                                        
                                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-50">
                                            <h4 class="font-display font-black uppercase tracking-widest text-[10px] text-Alumco-blue">Historial Académico</h4>
                                            <span class="text-[9px] font-bold text-gray-300 bg-gray-50 px-2 py-0.5 rounded-full">{{ $user->certificados->count() }} total</span>
                                        </div>
                                        
                                        <div class="space-y-3 max-h-56 overflow-y-auto custom-scrollbar pr-2 pointer-events-auto">
                                            @foreach($user->certificados->sortByDesc('fecha_emision') as $cert)
                                                <div class="flex items-start gap-3 p-2 rounded-xl transition-colors {{ in_array($cert->curso_id, $selectedCursos) ? 'bg-Alumco-blue/5 border border-Alumco-blue/10' : '' }}">
                                                    <div class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0 {{ in_array($cert->curso_id, $selectedCursos) ? 'bg-Alumco-blue animate-pulse' : 'bg-gray-200' }}"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[11px] leading-tight {{ in_array($cert->curso_id, $selectedCursos) ? 'font-black text-Alumco-blue' : 'font-bold text-Alumco-gray' }}">
                                                            {{ $cert->curso->titulo }}
                                                        </p>
                                                        <p class="text-[9px] text-Alumco-gray/40 font-medium mt-1">Aprobado el {{ $cert->fecha_emision->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        {{-- Flechita --}}
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-[8px] border-transparent border-t-white drop-shadow-[0_1px_0_rgba(0,0,0,0.05)]"></div>
                                    </div>
                                </template>
                            </div>
                        @endif
                    </td>
                    @if($cursoSeleccionado)
                        <td class="px-8 py-5 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                {{ $user->certificados->where('curso_id', $cursoSeleccionado->id)->first()?->fecha_emision?->format('d/m/Y') ?? '—' }}
                            </span>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-16 text-center text-Alumco-gray/40">
                        <div class="flex flex-col items-center opacity-40">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="font-display font-bold uppercase tracking-widest text-xs">No se encontraron resultados para los filtros aplicados</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages())
    <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .custom-slider {
        pointer-events: none;
    }
    .custom-slider::-webkit-slider-thumb {
        pointer-events: auto;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #205099;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        -webkit-appearance: none;
    }
    .custom-slider::-moz-range-thumb {
        pointer-events: auto;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #205099;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('modals')
<!-- Modal Configuración Exportación -->
<div id="export-modal-backdrop" class="fixed inset-0 bg-Alumco-gray/40 backdrop-blur-sm z-50 hidden opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true"></div>
<div id="export-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden opacity-0 pointer-events-none transition-all duration-300 scale-95" aria-hidden="true">
    <div class="bg-white w-full max-w-7xl rounded-3xl shadow-xl overflow-hidden" 
         x-data="{
            allCols: {
                rut:       { label: 'RUT', data: ['12.345.678-9', '18.765.432-K', '21.098.765-4'] },
                nombre:    { label: 'Nombre completo', data: ['Juan Pérez', 'María Ignacia', 'Carlos Ruiz'] },
                sexo:      { label: 'Sexo', data: ['Masculino', 'Femenino', 'Masculino'] },
                edad:      { label: 'Edad', data: ['28 años', '34 años', '45 años'] },
                email:     { label: 'Correo', data: ['j.perez@alumco.cl', 'm.ignacia@alumco.cl', 'c.ruiz@alumco.cl'] },
                sede:      { label: 'Sede', data: ['Sede Central', 'Sede Norte', 'Sede Sur'] },
                estamento: { label: 'Estamento', data: ['Auxiliares', 'Enfermería', 'Directivos'] },
                cursos:    { label: 'Cursos Aprobados', data: ['Curso A (20/04)', 'Curso B (15/04)', '—'] }
            },
            selectedKeys: ['rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos'],
            
            toggleCol(key) {
                if (this.selectedKeys.includes(key)) {
                    this.selectedKeys = this.selectedKeys.filter(k => k !== key);
                } else {
                    this.selectedKeys.push(key);
                }
            },
            
            reorder(fromIdx, toIdx) {
                const item = this.selectedKeys.splice(fromIdx, 1)[0];
                this.selectedKeys.splice(toIdx, 0, item);
            },

            resetToDefault() {
                this.selectedKeys = ['rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos'];
            }
         }"
         @open-export-modal.window="resetToDefault()">
        <form action="{{ route('admin.reportes.exportar') }}" method="GET">
            {{-- Replicar filtros actuales --}}
            @foreach(request()->except(['columnas', 'nombres']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            {{-- Inputs ocultos para enviar el orden final al servidor --}}
            <template x-for="key in selectedKeys" :key="key">
                <input type="hidden" name="columnas[]" :value="key">
            </template>

            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-full bg-Alumco-green/10 text-Alumco-blue flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-black text-Alumco-blue">Configurar Exportación</h3>
                    </div>
                </div>

                {{-- Paso 1: Selección de Columnas --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-Alumco-blue text-white flex items-center justify-center text-[9px]">1</span>
                            Seleccionar columnas a incluir
                        </h4>
                        <button type="button" @click="resetToDefault()" class="text-[16px] font-black uppercase text-Alumco-blue hover:text-Alumco-coral transition-colors flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Restaurar
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(info, key) in allCols" :key="key">
                            <button type="button" 
                                    @click="toggleCol(key)"
                                    :class="selectedKeys.includes(key) ? 'bg-Alumco-blue text-white border-Alumco-blue shadow-md' : 'bg-white text-Alumco-gray/40 border-gray-100 hover:border-Alumco-blue/20'"
                                    class="px-4 py-2 rounded-xl border text-xs font-bold transition-all flex items-center gap-2 active:scale-95">
                                <svg x-show="selectedKeys.includes(key)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <svg x-show="!selectedKeys.includes(key)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span x-text="info.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
                
                {{-- Paso 2: Vista Previa y Orden --}}
                <div x-show="selectedKeys.length > 0">
                    <h4 class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-Alumco-blue text-white flex items-center justify-center text-[9px]">2</span>
                        Vista previa y orden de columnas
                    </h4>
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-x-auto shadow-inner custom-scrollbar">
                        <div id="column-sortable-list" class="flex min-w-max bg-white">
                            <template x-for="(key, index) in selectedKeys" :key="key">
                                <div class="column-drag-item flex flex-col min-w-[180px] border-r border-gray-100 last:border-r-0 bg-white group transition-all" 
                                     draggable="true" 
                                     :data-key="key"
                                     @dragstart.stop="$event.dataTransfer.setData('fromIdx', index); $event.target.classList.add('opacity-40')"
                                     @dragend.stop="$event.target.classList.remove('opacity-40')"
                                     @dragover.prevent
                                     @drop.stop="const from = $event.dataTransfer.getData('fromIdx'); reorder(parseInt(from), index)">
                                    
                                    {{-- Cabecera --}}
                                    <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between gap-3 relative">
                                        <span class="text-[11px] font-display font-black text-Alumco-blue uppercase tracking-widest whitespace-nowrap" x-text="allCols[key].label"></span>

                                        <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-Alumco-blue/40 transition-colors">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 7a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm6-12a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                        </div>
                                    </div>

                                    {{-- Datos de Ejemplo --}}
                                    <template x-for="dataText in allCols[key].data">
                                        <div class="px-5 py-3.5 text-xs text-Alumco-gray/60 border-b border-gray-50 last:border-b-0 whitespace-nowrap overflow-hidden text-ellipsis" x-text="dataText"></div>
                                    </template>
                                    
                                    {{-- Pie --}}
                                    <div class="px-5 py-2 bg-gray-50/20 text-[9px] font-bold text-gray-300 uppercase tracking-tighter" x-text="'Columna: ' + key"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div x-show="selectedKeys.length === 0" class="py-20 text-center border-2 border-dashed border-gray-100 rounded-3xl">
                    <p class="text-Alumco-gray/40 font-bold uppercase tracking-widest text-xs">Selecciona al menos una columna para ver la vista previa</p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-100 flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-[10px] text-amber-800 font-medium leading-relaxed uppercase tracking-wider">
                        El archivo Excel final respetará exactamente el orden de izquierda a derecha configurado arriba.
                    </p>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex items-center justify-between gap-3">
                <div class="flex-1">
                    <livewire:admin.reporte-presets />
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeExportModal()" class="px-6 py-2.5 text-sm font-bold text-Alumco-gray/50 hover:text-Alumco-coral transition-colors text-center font-display">Cancelar</button>
                    <button type="submit" 
                            :disabled="selectedKeys.length === 0"
                            class="bg-Alumco-green hover:bg-Alumco-green-vivid text-Alumco-blue font-display font-bold py-3 px-10 rounded-xl shadow-lg shadow-Alumco-green/20 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-30 disabled:pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Generar Reporte Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const expBackdrop = document.getElementById('export-modal-backdrop');
    const expModal = document.getElementById('export-modal');
    let isExpOpen = false;

    function openExportModal() {
        if (!expBackdrop || !expModal) return;
        window.dispatchEvent(new CustomEvent('open-export-modal'));
        expBackdrop.classList.remove('hidden');
        expModal.classList.remove('hidden');
        void expModal.offsetWidth;
        expBackdrop.classList.remove('opacity-0', 'pointer-events-none');
        expModal.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
        expModal.classList.add('scale-100');
        isExpOpen = true;
    }

    function closeExportModal() {
        if (!expBackdrop || !expModal) return;
        expBackdrop.classList.add('opacity-0', 'pointer-events-none');
        expModal.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        expModal.classList.remove('scale-100');
        setTimeout(() => {
            if (!isExpOpen) {
                expBackdrop.classList.add('hidden');
                expModal.classList.add('hidden');
            }
        }, 300);
        isExpOpen = false;
    }

    expBackdrop?.addEventListener('click', closeExportModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isExpOpen) closeExportModal(); });

    document.addEventListener('DOMContentLoaded', function () {
        // --- RANGO ETARIO ---
        const ageRoot = document.getElementById('age-filter-root');
        if (ageRoot) {
            const minSlider = document.getElementById('age-min-slider');
            const maxSlider = document.getElementById('age-max-slider');
            const minValue = document.getElementById('age-min-value');
            const maxValue = document.getElementById('age-max-value');
            const fill = document.getElementById('age-range-fill');
            const minInput = document.getElementById('edad-min-input');
            const maxInput = document.getElementById('edad-max-input');
            
            const minRange = parseInt(minSlider.min);
            const maxRange = parseInt(maxSlider.max);

            const updateRange = () => {
                let v1 = parseInt(minSlider.value);
                let v2 = parseInt(maxSlider.value);
                
                // Asegurar que v1 sea el menor y v2 el mayor
                if (v1 > v2) {
                    [v1, v2] = [v2, v1];
                }
                
                fill.style.left = ((v1 - minRange) / (maxRange - minRange) * 100) + '%';
                fill.style.width = ((v2 - v1) / (maxRange - minRange) * 100) + '%';
                
                minValue.textContent = v1;
                maxValue.textContent = v2;
                
                minInput.value = v1;
                maxInput.value = v2;
            };

            minSlider.oninput = updateRange;
            maxSlider.oninput = updateRange;
            
            // Inicializar
            updateRange();
        }
    });
</script>
@endpush
