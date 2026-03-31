@extends('layouts.admin')

@section('title', 'Panel de administración')

@section('content')
<div class="flex justify-between items-end mb-8">
    <div>
        <h2 class="text-[32px] font-bold text-Alumco-gray mb-1">Reporte de Capacitaciones</h2>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.reportes.exportar', request()->query()) }}"
            class="bg-Alumco-green hover:bg-opacity-90 text-Alumco-blue font-bold py-2 px-4 rounded-Alumco shadow flex items-center transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Descargar reporte Excel
        </a>
    </div>
</div>

<div class="mb-8">
    @php
        $selectedSedes = array_map('intval', (array) request()->input('sede_id', []));
        $selectedEstamentos = array_map('intval', (array) request()->input('estamento_id', []));
        $selectedCursos = array_map('intval', (array) request()->input('curso_id', []));
        $edadMinGlobal = $ageBounds['min'] ?? 0;
        $edadMaxGlobal = $ageBounds['max'] ?? 120;
        $edadMinInicial = request()->filled('edad_min') ? (int) request('edad_min') : $edadMinGlobal;
        $edadMaxInicial = request()->filled('edad_max') ? (int) request('edad_max') : $edadMaxGlobal;
        $edadActiva = request()->filled('edad_min') || request()->filled('edad_max');

        $edadMinInicial = max($edadMinGlobal, min($edadMinInicial, $edadMaxGlobal));
        $edadMaxInicial = max($edadMinGlobal, min($edadMaxInicial, $edadMaxGlobal));
        if ($edadMinInicial > $edadMaxInicial) {
            [$edadMinInicial, $edadMaxInicial] = [$edadMaxInicial, $edadMinInicial];
        }
    @endphp

    <form action="{{ route('admin.reportes.index') }}" method="GET" class="bg-white border border-Alumco-gray/15 rounded-Alumco p-4 md:p-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-4 filter-card">
                <label class="block text-xs uppercase tracking-wide font-bold text-Alumco-gray/75 mb-2">Estamento</label>
                <select name="estamento_id[]" multiple class="w-full min-h-[140px] border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 bg-white text-Alumco-gray focus-ring">
                    @foreach($estamentos as $estamento)
                    <option value="{{ $estamento->id }}" {{ in_array((int) $estamento->id, $selectedEstamentos, true) ? 'selected' : '' }}>
                        {{ $estamento->nombre }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-Alumco-gray/70 mt-2">Puedes seleccionar uno o varios estamentos.</p>
            </div>

            <div class="lg:col-span-4 filter-card relative" id="course-filter-root">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs uppercase tracking-wide font-bold text-Alumco-gray/75">Cursos (debe cumplir todos)</label>
                    <button type="button" id="course-clear-btn" class="text-xs font-semibold text-Alumco-blue hover:underline">Limpiar</button>
                </div>

                <button type="button" id="course-picker-trigger" aria-expanded="false" aria-controls="course-picker-panel"
                    class="w-full flex items-center justify-between gap-3 border border-Alumco-gray/30 rounded-Alumco px-3 py-2 bg-white text-left focus-ring">
                    <span id="course-picker-summary" class="text-sm font-medium text-Alumco-gray">Selecciona uno o mas cursos</span>
                    <span id="course-picker-count" class="inline-flex items-center justify-center rounded-full bg-Alumco-blue text-white text-xs font-bold px-2 py-0.5 min-w-[28px]">0</span>
                </button>

                <div id="selected-course-chips" class="mt-2 flex flex-wrap gap-2"></div>

                <div id="course-picker-panel" class="hidden absolute top-[calc(100%+10px)] left-0 right-0 z-30">
                    <div class="bg-white rounded-Alumco border border-Alumco-gray/20 shadow-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-bold text-Alumco-gray">Seleccion de cursos</h3>
                            <button type="button" id="course-picker-close" class="text-Alumco-gray/75 hover:text-Alumco-gray text-sm font-semibold">Cerrar</button>
                        </div>

                        <input type="text" id="course-search" placeholder="Buscar curso..." class="w-full border border-Alumco-gray/30 rounded-Alumco px-3 py-2 text-sm mb-3 focus-ring">

                        <div id="course-options-list" class="max-h-64 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                            @foreach($cursos as $curso)
                            <label class="course-option flex items-center gap-3 rounded-Alumco border border-transparent hover:border-Alumco-blue/30 hover:bg-Alumco-cream px-2 py-2 cursor-pointer"
                                data-course-title="{{ strtolower($curso->titulo) }}">
                                <input type="checkbox" name="curso_id[]" value="{{ $curso->id }}" data-course-option data-course-label="{{ $curso->titulo }}"
                                    class="h-4 w-4 accent-Alumco-blue" {{ in_array((int) $curso->id, $selectedCursos, true) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-Alumco-gray">{{ $curso->titulo }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <noscript>
                    <div class="mt-3">
                        <label class="block text-sm font-semibold text-Alumco-gray mb-1">Seleccion multiple (modo basico)</label>
                        <select name="curso_id[]" multiple class="w-full min-h-[120px] border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 bg-white text-Alumco-gray">
                            @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ in_array((int) $curso->id, $selectedCursos, true) ? 'selected' : '' }}>{{ $curso->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                </noscript>
            </div>

            <div class="lg:col-span-4 filter-card">
                <label class="block text-xs uppercase tracking-wide font-bold text-Alumco-gray/75 mb-2">Sede</label>
                <select name="sede_id[]" multiple class="w-full min-h-[140px] border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 bg-white text-Alumco-gray focus-ring">
                    @foreach($sedes as $sede)
                    <option value="{{ $sede->id }}" {{ in_array((int) $sede->id, $selectedSedes, true) ? 'selected' : '' }}>
                        {{ $sede->nombre }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-Alumco-gray/70 mt-2">Puedes seleccionar una o varias sedes.</p>
            </div>

            <div class="lg:col-span-6 filter-card" id="age-filter-root" data-age-enabled="{{ $edadActiva ? '1' : '0' }}">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs uppercase tracking-wide font-bold text-Alumco-gray/75">Rango etario</label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-Alumco-gray cursor-pointer">
                        <input type="checkbox" id="age-filter-toggle" class="h-4 w-4 accent-Alumco-blue" {{ $edadActiva ? 'checked' : '' }}>
                        Aplicar
                    </label>
                </div>

                <div id="age-slider-wrapper">
                    <div class="dual-range mb-2">
                        <div class="dual-range__track"></div>
                        <div class="dual-range__fill" id="age-range-fill"></div>
                        <input type="range" id="age-min-slider" min="{{ $edadMinGlobal }}" max="{{ $edadMaxGlobal }}" value="{{ $edadMinInicial }}">
                        <input type="range" id="age-max-slider" min="{{ $edadMinGlobal }}" max="{{ $edadMaxGlobal }}" value="{{ $edadMaxInicial }}">
                    </div>

                    <div class="flex items-center justify-between text-sm font-semibold text-Alumco-gray">
                        <span>Min: <span id="age-min-value">{{ $edadMinInicial }}</span> años</span>
                        <span>Max: <span id="age-max-value">{{ $edadMaxInicial }}</span> años</span>
                    </div>

                    <p class="text-xs text-Alumco-gray/70 mt-1">Rango disponible: {{ round($edadMinGlobal) }} a {{ round($edadMaxGlobal) }} años.</p>
                </div>

                <input type="hidden" id="edad-min-input" name="edad_min" value="{{ request('edad_min') }}">
                <input type="hidden" id="edad-max-input" name="edad_max" value="{{ request('edad_max') }}">

                <noscript>
                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <input type="number" min="0" max="120" name="edad_min" value="{{ request('edad_min') }}" class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2" placeholder="Edad min">
                        <input type="number" min="0" max="120" name="edad_max" value="{{ request('edad_max') }}" class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2" placeholder="Edad max">
                    </div>
                </noscript>
            </div>

            <div class="lg:col-span-6 filter-card">
                <label class="block text-xs uppercase tracking-wide font-bold text-Alumco-gray/75 mb-2">Certificacion</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-Alumco-gray mb-1">Aprobado desde</label>
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                            class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 text-Alumco-gray focus-ring">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-Alumco-gray mb-1">Aprobado hasta</label>
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                            class="w-full border-Alumco-gray/30 rounded-Alumco shadow-sm border p-2 text-Alumco-gray focus-ring">
                    </div>
                </div>
            </div>

            <div class="lg:col-span-12 flex flex-wrap items-center gap-2">
                @php
                    $activeFiltersCount = 0;
                    if (count($selectedSedes) > 0) { $activeFiltersCount++; }
                    if (count($selectedEstamentos) > 0) { $activeFiltersCount++; }
                    if (count($selectedCursos) > 0) { $activeFiltersCount++; }
                    if ($edadActiva) { $activeFiltersCount++; }
                    if (request()->filled('fecha_inicio') || request()->filled('fecha_fin')) { $activeFiltersCount++; }
                @endphp
                <span class="inline-flex items-center rounded-full bg-Alumco-blue/10 text-Alumco-blue text-sm font-bold px-3 py-1">
                    {{ $activeFiltersCount }} filtros activos
                </span>
            </div>

            <div class="lg:col-span-12 flex flex-wrap gap-2 pt-1">
                <button type="submit" class="bg-Alumco-blue hover:bg-opacity-90 text-white font-bold py-2.5 px-7 rounded-Alumco shadow transition-colors">Filtrar</button>
                <a href="{{ route('admin.reportes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-Alumco-gray font-bold py-2.5 px-7 rounded-Alumco shadow transition-colors flex items-center">Limpiar todo</a>
            </div>
        </div>
    </form>
</div>

<div class="mb-3 rounded-Alumco border border-Alumco-blue/20 bg-Alumco-blue/5 px-4 py-3 text-sm font-semibold text-Alumco-blue">
    {{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuario' : 'usuarios' }} encontrados.
</div>

<div class="bg-white rounded-Alumco border border-Alumco-gray/20 overflow-hidden shadow-sm">
    <table class="min-w-full leading-normal text-Alumco-gray">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Sexo</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Edad</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Sede</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Estamento</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Estado</th>
                <th class="px-5 py-3 border-b-2 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cursos Aprobados</th>
                @if($cursoSeleccionado)
                    <th class="px-5 py-3 border-b-2 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Progreso: {{ $cursoSeleccionado->titulo }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $user)
            <tr class="hover:bg-Alumco-cream/50 transition-colors">
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    <p class="font-bold whitespace-no-wrap">{{ $user->name }}</p>
                    <p class="text-xs opacity-75 mt-0.5">{{ $user->email }}</p>
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                    @php
                        $sexo = strtolower((string) ($user->sexo ?? ''));
                    @endphp
                    @if($sexo === 'm' || $sexo === 'masculino' || $sexo === 'hombre')
                    Masculino
                    @elseif($sexo === 'f' || $sexo === 'femenino' || $sexo === 'mujer')
                    Femenino
                    @elseif($sexo !== '')
                    {{ ucfirst($sexo) }}
                    @else
                    <span class="opacity-75 italic">No informado</span>
                    @endif
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    @if($user->fecha_nacimiento)
                    @php
                        $edad = \Carbon\Carbon::parse($user->fecha_nacimiento)->age;
                    @endphp
                    <p class="font-bold">{{ $edad }} años</p>
                    <p class="text-xs opacity-75">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') }}</p>
                    @else
                    <span class="opacity-75 italic">Sin fecha</span>
                    @endif
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                    <p class="whitespace-no-wrap">{{ $user->sede->nombre ?? 'N/A' }}</p>
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                    {{ $user->estamento->nombre ?? 'N/A' }}
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    @if($user->activo)
                    <span class="text-green-600 font-bold">Activo</span>
                    @else
                    <span class="text-red-600 font-bold">Inactivo</span>
                    @endif
                </td>
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    @forelse($user->certificados as $certificado)
                    <div class="mb-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-Alumco-green text-Alumco-blue">✓ {{ $certificado->curso->titulo }}</span>
                        <span class="text-xs opacity-75 ml-1">({{ $certificado->fecha_emision->format('d/m/Y') }})</span>
                    </div>
                    @empty
                    <span class="opacity-75 italic text-sm">Sin cursos aprobados</span>
                    @endforelse
                </td>

                @if($cursoSeleccionado)
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm min-w-[200px]">
                        @php
                            $totalModulos = $cursoSeleccionado->modulos_count;
                            $completados = $user->modulos_completados_count ?? 0;
                            $porcentaje = $totalModulos > 0 ? round(($completados / $totalModulos) * 100) : 0;
                        @endphp

                        <div class="flex items-center">
                            <span class="mr-2 text-gray-700 font-bold">{{ $porcentaje }}%</span>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 flex-1">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentaje }}%"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $completados }} de {{ $totalModulos }} módulos completados</p>
                    </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-8 border-b border-Alumco-gray/10 bg-transparent text-sm text-center opacity-75">
                    No se encontraron registros con esos filtros.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-3 bg-white border-t border-Alumco-gray/10 flex flex-col xs:flex-row items-center xs:justify-between">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pickerRoot = document.getElementById('course-filter-root');
        if (pickerRoot) {
            const trigger = document.getElementById('course-picker-trigger');
            const panel = document.getElementById('course-picker-panel');
            const closeBtn = document.getElementById('course-picker-close');
            const searchInput = document.getElementById('course-search');
            const clearBtn = document.getElementById('course-clear-btn');
            const summary = document.getElementById('course-picker-summary');
            const countBadge = document.getElementById('course-picker-count');
            const chipsContainer = document.getElementById('selected-course-chips');
            const options = Array.from(document.querySelectorAll('[data-course-option]'));

            const openPanel = () => {
                panel.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                if (searchInput) searchInput.focus();
            };

            const closePanel = () => {
                panel.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
            };

            const selectedItems = () => options.filter((option) => option.checked);

            const updatePickerView = () => {
                const selected = selectedItems();
                const total = selected.length;
                countBadge.textContent = String(total);

                if (total === 0) {
                    summary.textContent = 'Selecciona uno o mas cursos';
                } else if (total === 1) {
                    summary.textContent = selected[0].dataset.courseLabel;
                } else {
                    summary.textContent = total + ' cursos seleccionados';
                }

                chipsContainer.innerHTML = '';
                selected.slice(0, 8).forEach((option) => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'inline-flex items-center gap-2 rounded-full border border-Alumco-blue/30 bg-white text-Alumco-blue text-xs font-bold px-3 py-1 hover:bg-Alumco-cream';
                    chip.innerHTML = '<span>' + option.dataset.courseLabel + '</span><span aria-hidden="true">x</span>';
                    chip.addEventListener('click', function () {
                        option.checked = false;
                        updatePickerView();
                    });
                    chipsContainer.appendChild(chip);
                });

                if (total > 8) {
                    const extra = document.createElement('span');
                    extra.className = 'inline-flex items-center rounded-full bg-Alumco-gray/15 text-Alumco-gray text-xs font-bold px-3 py-1';
                    extra.textContent = '+' + (total - 8) + ' mas';
                    chipsContainer.appendChild(extra);
                }
            };

            const filterCourses = () => {
                const query = (searchInput.value || '').toLowerCase().trim();
                const labels = Array.from(document.querySelectorAll('.course-option'));
                labels.forEach((label) => {
                    const title = label.dataset.courseTitle || '';
                    label.classList.toggle('hidden', !title.includes(query));
                });
            };

            trigger.addEventListener('click', function () {
                if (panel.classList.contains('hidden')) openPanel(); else closePanel();
            });

            closeBtn.addEventListener('click', closePanel);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closePanel();
            });

            document.addEventListener('click', function (event) {
                if (!pickerRoot.contains(event.target) && !panel.classList.contains('hidden')) closePanel();
            });

            options.forEach((option) => option.addEventListener('change', updatePickerView));
            if (searchInput) searchInput.addEventListener('input', filterCourses);

            clearBtn.addEventListener('click', function () {
                options.forEach((option) => option.checked = false);
                if (searchInput) { searchInput.value = ''; filterCourses(); }
                updatePickerView();
            });

            updatePickerView();
        }

        const ageRoot = document.getElementById('age-filter-root');
        if (ageRoot) {
            const toggle = document.getElementById('age-filter-toggle');
            const minSlider = document.getElementById('age-min-slider');
            const maxSlider = document.getElementById('age-max-slider');
            const minValue = document.getElementById('age-min-value');
            const maxValue = document.getElementById('age-max-value');
            const fill = document.getElementById('age-range-fill');
            const minInput = document.getElementById('edad-min-input');
            const maxInput = document.getElementById('edad-max-input');
            const wrapper = document.getElementById('age-slider-wrapper');

            const minRange = parseInt(minSlider.min, 10);
            const maxRange = parseInt(minSlider.max, 10);
            const totalRange = Math.max(1, maxRange - minRange);

            const updateRangeFill = () => {
                let min = parseInt(minSlider.value, 10);
                let max = parseInt(maxSlider.value, 10);

                if (min > max) {
                    if (document.activeElement === minSlider) {
                        max = min; maxSlider.value = String(max);
                    } else {
                        min = max; minSlider.value = String(min);
                    }
                }

                fill.style.left = (((min - minRange) / totalRange) * 100) + '%';
                fill.style.width = (((max - min) / totalRange) * 100) + '%';
                minValue.textContent = String(min);
                maxValue.textContent = String(max);

                if (toggle.checked) {
                    minInput.value = String(min);
                    maxInput.value = String(max);
                }
            };

            const applyAgeEnabled = () => {
                const enabled = toggle.checked;
                wrapper.classList.toggle('range-disabled', !enabled);
                minSlider.disabled = !enabled; maxSlider.disabled = !enabled;
                if (enabled) {
                    minInput.value = minSlider.value; maxInput.value = maxSlider.value;
                } else {
                    minInput.value = ''; maxInput.value = '';
                }
            };

            minSlider.addEventListener('input', updateRangeFill);
            maxSlider.addEventListener('input', updateRangeFill);
            toggle.addEventListener('change', applyAgeEnabled);

            updateRangeFill();
            applyAgeEnabled();
        }
    });
</script>
@endpush
