@php
    $selectedSede = collect($sedes)->firstWhere('id', (int) $sedeId);
    $selectedEstamento = collect($estamentos)->firstWhere('id', (int) $estamentoId);
    $selectedCurso = collect($cursos)->firstWhere('id', (int) $cursoId);
    $activeFilterLabels = [
        $year,
        $selectedSede['nombre'] ?? 'Todas las sedes',
        $selectedEstamento['nombre'] ?? 'Todos los estamentos',
        $selectedCurso['titulo'] ?? 'Todas las capacitaciones',
    ];
    $chartConfigs = [
        'bi-certificates-trend' => $charts['certificatesTrend'],
        'bi-planning-trend' => $charts['planningTrend'],
        'bi-completion-trend' => $charts['completionTrend'],
        'bi-sede-coverage' => $charts['sedeCoverage'],
        'bi-sede-coverage-segments' => $charts['sedeCoverage'],
        'bi-estamento-mix' => $charts['estamentoMix'],
        'bi-evaluations' => $charts['evaluations'],
        'bi-feedback-category' => $charts['feedbackCategory'],
        'bi-progress-feedback-scatter' => $charts['progressFeedbackScatter'],
        'bi-age-distribution' => $charts['ageDistribution'],
    ];
@endphp

<div class="space-y-6 lg:space-y-8" data-filter-signature="{{ $filterSignature }}">
    {{-- Título y Encabezado --}}
    <div class="flex flex-col gap-2">
        <div class="max-w-3xl">
            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-Alumco-blue/60">Monitoreo y Métricas</p>
            <h1 class="font-display text-3xl font-black text-Alumco-blue">Dashboard analítico</h1>
        </div>
    </div>

    {{-- Header de Control & Filtros --}}
    <section class="admin-surface overflow-hidden">
        <div class="flex flex-col border-b border-slate-100 bg-white lg:flex-row lg:items-stretch">
            {{-- Resumen de Filtros Activos --}}
            <div class="flex flex-1 flex-col justify-center border-b border-slate-100 p-6 lg:border-b-0 lg:border-r">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/60">Filtros activos</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($activeFilterLabels as $filterLabel)
                        <span wire:key="active-filter-{{ $loop->index }}" class="inline-flex items-center rounded-md bg-Alumco-blue/5 px-2.5 py-1 text-[11px] font-bold text-Alumco-blue ring-1 ring-inset ring-Alumco-blue/10">
                            {{ $filterLabel }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Acciones & Status --}}
            <div class="flex flex-col gap-4 bg-slate-50/50 p-6 sm:flex-row sm:items-center sm:justify-between lg:min-w-[25rem] lg:justify-end lg:bg-transparent">
                <div class="flex items-center gap-3 sm:justify-end">
                    <div class="hidden h-10 w-px bg-slate-200 sm:block"></div>
                    <div class="text-left sm:text-right">
                        <p class="text-[9px] font-black uppercase tracking-[0.18em] text-Alumco-gray/40 leading-none">Última actualización</p>
                        <p class="mt-1.5 text-xs font-bold text-Alumco-blue leading-none">{{ $kpis['updated_at'] }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 sm:justify-end">
                    <button type="button" 
                            wire:click="resetFilters" 
                            title="Restablecer todos los filtros"
                            class="admin-icon-button h-10 w-10 shrink-0 !bg-white !text-Alumco-gray border-slate-200 hover:!border-red-800 hover:!text-red-800 motion-reduce:transition-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <a href="{{ route('admin.reportes.index') }}" wire:navigate.hover class="admin-action-button min-w-0 flex-1 !min-h-[2.5rem] !py-2 shadow-sm sm:flex-none motion-reduce:transition-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4V7m2 14H7a2 2 0 01-2-2V5" />
                        </svg>
                        Ver reportes
                    </a>
                </div>
            </div>
        </div>

        {{-- Selectores de Filtros --}}
        <div class="grid items-end gap-4 bg-slate-50/30 px-6 py-6 sm:gap-6 lg:grid-cols-12">
            <label class="block lg:col-span-2">
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">Año de planificación</span>
                <select wire:model.live="year" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-Alumco-blue shadow-sm transition-colors focus:border-Alumco-cyan focus:outline-none focus:ring-4 focus:ring-Alumco-cyan/10 motion-reduce:transition-none">
                    @foreach ($years as $availableYear)
                        <option wire:key="year-{{ $availableYear }}" value="{{ $availableYear }}">{{ $availableYear }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block lg:col-span-3">
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">Sede Institucional</span>
                <select wire:model.live="sedeId" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-Alumco-blue shadow-sm transition-colors focus:border-Alumco-cyan focus:outline-none focus:ring-4 focus:ring-Alumco-cyan/10 motion-reduce:transition-none">
                    <option value="">Todas las sedes</option>
                    @foreach ($sedes as $sede)
                        <option wire:key="sede-{{ $sede['id'] }}" value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block lg:col-span-3">
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">Estamento / Rol</span>
                <select wire:model.live="estamentoId" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-Alumco-blue shadow-sm transition-colors focus:border-Alumco-cyan focus:outline-none focus:ring-4 focus:ring-Alumco-cyan/10 motion-reduce:transition-none">
                    <option value="">Todos los estamentos</option>
                    @foreach ($estamentos as $estamento)
                        <option wire:key="estamento-{{ $estamento['id'] }}" value="{{ $estamento['id'] }}">{{ $estamento['nombre'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block lg:col-span-4">
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">Capacitación específica</span>
                <select wire:model.live="cursoId" class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-Alumco-blue shadow-sm transition-colors focus:border-Alumco-cyan focus:outline-none focus:ring-4 focus:ring-Alumco-cyan/10 motion-reduce:transition-none">
                    <option value="">Todas las capacitaciones</option>
                    @foreach ($cursos as $curso)
                        <option wire:key="curso-{{ $curso['id'] }}" value="{{ $curso['id'] }}">{{ $curso['titulo'] }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    {{-- Navegación de Vistas --}}
    <nav class="w-full" aria-label="Vistas del dashboard">
        <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-white p-1 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($availableViews as $viewId => $view)
                <button
                    type="button"
                    wire:key="bi-view-{{ $viewId }}"
                    wire:click="setView('{{ $viewId }}')"
                    @class([
                        'relative flex min-h-[4.25rem] items-center gap-3 rounded-lg px-4 py-3 text-left transition-colors duration-200 data-loading:opacity-60 motion-reduce:transition-none',
                        'bg-Alumco-blue text-white shadow-md' => $activeView === $viewId,
                        'text-Alumco-gray hover:bg-slate-50 hover:text-Alumco-blue' => $activeView !== $viewId,
                    ])
                >
                    <div @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                        'bg-white/10' => $activeView === $viewId,
                        'bg-slate-100' => $activeView !== $viewId,
                    ])>
                        @switch($viewId)
                            @case('executive')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                @break
                            @case('progress')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                @break
                            @case('quality')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @break
                            @case('segments')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                @break
                        @endswitch
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-wider">{{ $view['label'] }}</span>
                        <span @class([
                            'mt-0.5 block text-[10px] font-bold leading-tight',
                            'text-white/70' => $activeView === $viewId,
                            'text-Alumco-gray/50' => $activeView !== $viewId,
                        ])>{{ $view['description'] }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </nav>

    {{-- KPIs contextuales por vista --}}
    <section class="grid grid-cols-1 gap-4 transition-opacity sm:grid-cols-2 xl:grid-cols-4 motion-reduce:transition-none" wire:loading.class="opacity-60">
        @foreach ($contextKpis as $contextKpi)
            <article
                wire:key="context-kpi-{{ $activeView }}-{{ $loop->index }}"
                @class([
                    'admin-surface flex min-h-40 min-w-0 flex-col justify-between rounded-lg border-t-4 p-5 sm:p-6',
                    'border-t-Alumco-blue' => $contextKpi['tone'] === 'neutral',
                    'border-t-emerald-700 bg-emerald-50/30' => $contextKpi['tone'] === 'positive',
                    'border-t-amber-600 bg-amber-50/40' => $contextKpi['tone'] === 'warning',
                    'border-t-red-800 bg-red-50/50' => $contextKpi['tone'] === 'risk',
                    'border-t-slate-200 bg-slate-50/60' => $contextKpi['tone'] === 'muted',
                ])
            >
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-Alumco-gray/45">{{ $contextKpi['label'] }}</p>
                    <p @class([
                        'mt-4 break-words font-display text-3xl font-black leading-none sm:text-4xl',
                        'text-red-800' => $contextKpi['tone'] === 'risk',
                        'text-emerald-800' => $contextKpi['tone'] === 'positive',
                        'text-amber-800' => $contextKpi['tone'] === 'warning',
                        'text-Alumco-gray/45' => $contextKpi['tone'] === 'muted',
                        'text-Alumco-blue' => in_array($contextKpi['tone'], ['neutral'], true),
                    ])>{{ $contextKpi['value'] }}</p>
                </div>
                <p class="mt-6 text-xs font-semibold leading-5 text-Alumco-gray/60">{{ $contextKpi['detail'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="bi-view-panel transition-opacity motion-reduce:transition-none" wire:key="bi-content-{{ $activeView }}" wire:transition="bi-dashboard-view" wire:loading.class="opacity-60">
        @if ($activeView === 'executive')
            <section class="grid gap-8 xl:grid-cols-12">
                <div class="space-y-8 xl:col-span-8">
                    <x-admin.chart-panel 
                        eyebrow="Tendencia histórica" 
                        title="¿Cómo ha evolucionado la certificación respecto al año anterior?" 
                        description="Comparativa mensual con promedio del ciclo como referencia para evitar sobrerreaccionar a picos aislados." 
                        badge="Línea" 
                        canvas-class="chart-panel__canvas chart-panel__canvas--lg">
                        <div class="h-80" wire:ignore>
                            <canvas id="bi-certificates-trend"></canvas>
                        </div>
                    </x-admin.chart-panel>

                    <section class="admin-surface rounded-lg p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/45">Reglas de lectura</p>
                        <div class="mt-4 grid gap-4 text-sm font-semibold text-Alumco-gray/70 md:grid-cols-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-700"></span>
                                Logrado o sobre meta
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-800"></span>
                                Requiere seguimiento
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-Alumco-blue"></span>
                                Información operacional
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="space-y-8 xl:col-span-4">
                    <section class="admin-surface rounded-lg p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/45">Resumen operativo</p>
                        <h3 class="mt-3 font-display text-xl font-black text-Alumco-blue">Indicadores de gestión</h3>
                        <dl class="mt-8 space-y-6">
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <dt class="text-sm font-semibold text-Alumco-gray/70">Total certificados</dt>
                                <dd class="text-lg font-black text-Alumco-blue">{{ number_format($kpis['certificates']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <dt class="text-sm font-semibold text-Alumco-gray/70">Tasa de aprobación</dt>
                                <dd class="text-lg font-black text-emerald-800">{{ $kpis['approval_rate'] }}%</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-sm font-semibold text-Alumco-gray/70">Satisfacción (NPS)</dt>
                                <dd @class([
                                    'text-lg font-black',
                                    'text-Alumco-blue' => $kpis['feedback_average'] !== null,
                                    'text-Alumco-gray/45' => $kpis['feedback_average'] === null,
                                ])>{{ $kpis['feedback_average'] ?? 'Sin datos' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="admin-surface rounded-lg p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/45">Ranking ejecutivo</p>
                        <h3 class="mt-3 font-display text-xl font-black text-Alumco-blue">Sedes con mayor alcance</h3>
                        <div class="mt-8 space-y-6">
                            @forelse ($rankings['topSedes'] as $sede)
                                <div wire:key="executive-sede-{{ $sede['label'] }}">
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-bold text-Alumco-blue">{{ $sede['label'] }}</span>
                                        <span class="font-black text-Alumco-blue">{{ $sede['percentage'] }}%</span>
                                    </div>
                                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-Alumco-blue" style="width: {{ max($sede['percentage'], 3) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg bg-slate-50 px-4 py-5 text-sm font-semibold text-Alumco-gray/60">Sin datos suficientes para construir el ranking con los filtros actuales.</div>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </section>
        @elseif ($activeView === 'progress')
            <section class="grid gap-8 xl:grid-cols-12">
                <div class="space-y-8 xl:col-span-8">
                    <x-admin.chart-panel 
                        eyebrow="Embudo de Conversión" 
                        title="¿Cómo fluyen los usuarios desde el inicio hasta la certificación?" 
                        description="Comparativa mensual de nuevos registros vs emisiones exitosas." 
                        badge="Línea" 
                        canvas-class="chart-panel__canvas chart-panel__canvas--lg">
                        <div class="h-80" wire:ignore>
                            <canvas id="bi-completion-trend"></canvas>
                        </div>
                    </x-admin.chart-panel>

                    <section class="admin-surface overflow-hidden">
                        <div class="border-b border-slate-100 px-8 py-6">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/45">Cursos críticos</p>
                            <h3 class="mt-4 font-display text-xl font-black text-Alumco-blue">¿Qué capacitaciones requieren seguimiento inmediato?</h3>
                        </div>

                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="min-w-full divide-y divide-slate-100 text-left">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">Curso / Capacitación</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-center">Progreso</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">Cumplimiento</th>
                                        <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-right">Calidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white text-sm">
                                    @forelse ($rankings['criticalCourses'] as $course)
                                        <tr wire:key="critical-course-{{ $course['id'] }}" class="transition-colors hover:bg-slate-50/30 motion-reduce:transition-none">
                                            <td class="px-8 py-6">
                                                <p class="max-w-md font-black text-Alumco-blue">{{ $course['title'] }}</p>
                                                <p class="mt-1 text-[11px] font-bold uppercase text-red-800">{{ number_format($course['risk']) }} pendientes</p>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="font-black text-Alumco-blue">{{ number_format($course['started']) }}</span>
                                                    <span class="text-[10px] font-bold text-Alumco-gray/40 uppercase">Iniciaron</span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex min-w-36 items-center gap-4">
                                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full rounded-full bg-Alumco-blue" style="width: {{ max($course['completion'], 3) }}%"></div>
                                                    </div>
                                                    <span class="font-black text-Alumco-blue">{{ $course['completion'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <span @class([
                                                    'font-black',
                                                    'text-red-800' => ($course['feedback'] ?? 0) < 4 && $course['feedback'] !== null,
                                                    'text-Alumco-blue' => ($course['feedback'] ?? 0) >= 4 || $course['feedback'] === null,
                                                    'text-Alumco-gray/45' => $course['feedback'] === null,
                                                ])>
                                                    {{ $course['feedback'] ?? 'Sin datos' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-8 py-16 text-center">
                                                <p class="text-sm font-semibold text-Alumco-gray/60">No se identificaron cursos críticos con los filtros actuales.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="admin-surface p-8 xl:col-span-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Embudo de Formación</p>
                    <h3 class="mt-4 font-display text-xl font-black text-Alumco-blue">Distribución de Usuarios</h3>

                    <div class="mt-10 space-y-8">
                        @foreach ($funnel as $stage)
                            <div wire:key="funnel-stage-{{ $stage['label'] }}">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-bold text-Alumco-gray/70">{{ $stage['label'] }}</span>
                                    <span class="font-black text-Alumco-blue">{{ number_format($stage['value']) }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div @class([
                                        'h-full rounded-full shadow-sm',
                                        'bg-red-800' => $stage['label'] === 'En riesgo',
                                        'bg-Alumco-blue' => $stage['label'] !== 'En riesgo',
                                    ]) style="width: {{ max($stage['percentage'], 3) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </section>
        @elseif ($activeView === 'quality')
            <section class="grid gap-8 xl:grid-cols-12">
                <div class="space-y-8 xl:col-span-8">
                    <x-admin.chart-panel 
                        eyebrow="Desempeño en Evaluaciones" 
                        title="¿Cuál es la efectividad de las evaluaciones?" 
                        description="Comparativa de intentos aprobados vs reprobados en el periodo." 
                        badge="Barras">
                        <div class="h-80" wire:ignore>
                            <canvas id="bi-evaluations"></canvas>
                        </div>
                    </x-admin.chart-panel>

                    <x-admin.chart-panel 
                        eyebrow="Satisfacción del Usuario" 
                        title="¿Cómo valoran los usuarios la calidad de los cursos?" 
                        description="Rating promedio desglosado por categorías de feedback." 
                        badge="Barras">
                        <div class="h-80" wire:ignore>
                            <canvas id="bi-feedback-category"></canvas>
                        </div>
                    </x-admin.chart-panel>
                </div>

                <div class="space-y-8 xl:col-span-4">
                    <x-admin.chart-panel 
                        eyebrow="Correlación" 
                        title="¿Influye la satisfacción en el cumplimiento?" 
                        description="Relación entre el porcentaje de avance y la valoración otorgada." 
                        badge="Dispersión">
                        <div class="h-80" wire:ignore>
                            <canvas id="bi-progress-feedback-scatter"></canvas>
                        </div>
                    </x-admin.chart-panel>

                    <section class="admin-surface p-8">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Métricas de Calidad</p>
                        <dl class="mt-8 grid gap-6">
                            <div class="rounded-2xl bg-slate-50/50 p-6 ring-1 ring-inset ring-slate-100">
                                <dt class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">Tasa de Aprobación Global</dt>
                                <dd class="mt-4 text-4xl font-black text-Alumco-blue">{{ $kpis['approval_rate'] }}%</dd>
                            </div>
                            <div class="rounded-2xl bg-slate-50/50 p-6 ring-1 ring-inset ring-slate-100">
                                <dt class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">NPS Promedio</dt>
                                <dd @class([
                                    'mt-4 text-4xl font-black',
                                    'text-Alumco-blue' => $kpis['feedback_average'] !== null,
                                    'text-Alumco-gray/45' => $kpis['feedback_average'] === null,
                                ])>{{ $kpis['feedback_average'] ?? 'Sin datos' }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </section>
        @else
            <section class="grid gap-8 xl:grid-cols-12">
                <div class="space-y-8 xl:col-span-8">
                    <x-admin.chart-panel 
                        eyebrow="Segmentación" 
                        title="¿Cómo se distribuye la certificación por sede?" 
                        description="Análisis comparativo de cobertura institucional por región y campus." 
                        badge="Barras" 
                        canvas-class="chart-panel__canvas chart-panel__canvas--lg">
                        <div class="h-96" wire:ignore>
                            <canvas id="bi-sede-coverage-segments"></canvas>
                        </div>
                    </x-admin.chart-panel>
                </div>

                <div class="space-y-8 xl:col-span-4">
                    <x-admin.chart-panel 
                        eyebrow="Composición" 
                        title="¿Cuál es el perfil de nuestros usuarios?" 
                        description="Desglose por estamentos y roles institucionales." 
                        badge="Barras">
                        <div class="h-72" wire:ignore>
                            <canvas id="bi-estamento-mix"></canvas>
                        </div>
                    </x-admin.chart-panel>

                    <x-admin.chart-panel 
                        eyebrow="Demografía" 
                        title="¿Qué rango etario predomina?" 
                        description="Distribución de la base activa por edades." 
                        badge="Barras">
                        <div class="h-72" wire:ignore>
                            <canvas id="bi-age-distribution"></canvas>
                        </div>
                    </x-admin.chart-panel>
                </div>
            </section>

            <section class="admin-surface overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-8 py-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/45">Analítica individual</p>
                        <h3 class="mt-3 font-display text-xl font-black text-Alumco-blue">Colaboradores con señales accionables</h3>
                    </div>
                    <p class="max-w-xl text-sm font-semibold leading-6 text-Alumco-gray/60">Ordenado para priorizar seguimiento: primero personas con actividad iniciada sin certificación y menor puntaje de avance.</p>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full divide-y divide-slate-100 text-left">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">Colaborador/a</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40">Segmento</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-center">Cobertura</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-center">Avance</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-center">Calidad</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-gray/40 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white text-sm">
                            @forelse ($collaborators as $collaborator)
                                <tr wire:key="collaborator-analytics-{{ $collaborator['id'] }}" class="transition-colors hover:bg-slate-50/30 motion-reduce:transition-none">
                                    <td class="px-8 py-6">
                                        <p class="font-black text-Alumco-blue">{{ $collaborator['name'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-Alumco-gray/50">{{ $collaborator['email'] }}</p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="font-bold text-Alumco-gray/70">{{ $collaborator['sede'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-Alumco-gray/50">{{ $collaborator['estamento'] }}</p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <p class="font-black text-Alumco-blue">{{ $collaborator['coverage'] }}%</p>
                                        <p class="mt-1 text-[10px] font-bold uppercase text-Alumco-gray/40">{{ $collaborator['certified_courses'] }}/{{ $collaborator['assigned_courses'] }} cursos</p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="mx-auto flex min-w-32 max-w-40 items-center gap-3">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-Alumco-blue" style="width: {{ max($collaborator['module_progress'], 3) }}%"></div>
                                            </div>
                                            <span class="font-black text-Alumco-blue">{{ $collaborator['module_progress'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <p class="font-black text-Alumco-blue">{{ $collaborator['approval'] }}%</p>
                                        <p class="mt-1 text-[10px] font-bold uppercase text-Alumco-gray/40">Feedback {{ $collaborator['feedback_average'] ?? 's/d' }}</p>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span @class([
                                            'inline-flex rounded-md px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] ring-1 ring-inset',
                                            'bg-red-50 text-red-800 ring-red-900/10' => $collaborator['status'] === 'Seguimiento',
                                            'bg-emerald-50 text-emerald-800 ring-emerald-900/10' => $collaborator['status'] === 'Al día',
                                            'bg-slate-50 text-Alumco-gray/60 ring-slate-200' => $collaborator['status'] === 'Sin inicio',
                                        ])>{{ $collaborator['status'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <p class="text-sm font-semibold text-Alumco-gray/60">No hay colaboradores para analizar con los filtros actuales.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <script type="application/json" data-bi-chart-configs>
        @json($chartConfigs)
    </script>

    @script
        <script>
            window.AlumcoBusinessIntelligenceCharts = window.AlumcoBusinessIntelligenceCharts || {};
            window.AlumcoBusinessIntelligenceCharts.render = () => {
                const chartConfigElement = document.querySelector('[data-bi-chart-configs]');

                if (! chartConfigElement?.textContent) {
                    return;
                }

                let charts = {};

                try {
                    charts = JSON.parse(chartConfigElement.textContent);
                } catch (error) {
                    console.error('No se pudieron leer las configuraciones del dashboard analítico.', error);
                    return;
                }

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        Object.entries(charts).forEach(([canvasId, config]) => {
                            window.AlumcoCharts?.render(canvasId, config);
                        });
                    });
                });
            };

            if (! window.AlumcoBusinessIntelligenceCharts.bound) {
                document.addEventListener('bi-dashboard-render-charts', () => {
                    window.AlumcoBusinessIntelligenceCharts.render();
                });
                window.AlumcoBusinessIntelligenceCharts.bound = true;
            }

            window.AlumcoBusinessIntelligenceCharts.render();
        </script>
    @endscript
</div>
