<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalendarioCapacitaciones extends Component
{
    // ── Vista ──────────────────────────────────────────────────────────────
    public string $modoVista = 'anual';      // 'anual' | 'mensual'

    // ── Mes actual (vista mensual) ─────────────────────────────────────────
    public int $mesActual;
    public int $anioActual;

    public bool $modoPlaneacion = false;
    public bool $mostrarModalPlanificacion = false;

    // ── Modal (campos comunes) ─────────────────────────────────────────────
    public ?int $editandoId = null;
    public ?int $cursoId = null;
    public string $fechaInicioPlan = '';
    public string $fechaFinPlan    = '';
    public string $notas           = '';

    // ── Modal en vista anual ───────────────────────────────────────────────
    public int $semanaInicioPlan = 1;
    public int $semanaFinPlan    = 1;

    // ── Datos vista mensual ────────────────────────────────────────────────
    /** @var array Weeks with day cells + positioned bars for calendar view */
    public array $semanasDelMes = [];
    public int $diasEnMes = 30;

    // ── Datos vista anual ──────────────────────────────────────────────────
    /** @var array Semanas con sus cursos planificados */
    public array $semanasDelAnio = [];
    /** @var array Meses con su span de semanas para el header */
    public array $mesesDelAnio  = [];

    // ── Búsqueda / sidebar ─────────────────────────────────────────────────
    public array $cursosDisponibles   = [];
    public array $cursosSinPlanificar = [];
    public string $busquedaSidebar    = '';
    public string $queryModal         = '';

    /*
     * Tailwind safelist — keep full class names so they survive purge:
     * bg-blue-500 bg-purple-600 bg-green-600 bg-orange-500 bg-rose-500
     * bg-teal-500 bg-indigo-500 bg-amber-500 bg-cyan-600 bg-pink-500
     */
    private const PALETTE = [
        'bg-blue-500', 'bg-purple-600', 'bg-green-600', 'bg-orange-500', 'bg-rose-500',
        'bg-teal-500', 'bg-indigo-500', 'bg-amber-500', 'bg-cyan-600', 'bg-pink-500',
    ];

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Lifecycle                                                            */
    /* ────────────────────────────────────────────────────────────────────── */

    public function mount(): void
    {
        $this->mesActual  = Carbon::now()->month;
        $this->anioActual = Carbon::now()->year;
        $this->cargarCursosDisponibles();
        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Navigation — vista                                                   */
    /* ────────────────────────────────────────────────────────────────────── */

    public function cambiarVista(string $modo): void
    {
        if (! in_array($modo, ['anual', 'mensual'], true)) {
            return;
        }
        $this->modoVista = $modo;
        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Navigation — mes (vista mensual)                                     */
    /* ────────────────────────────────────────────────────────────────────── */

    public function mesAnterior(): void
    {
        if ($this->mesActual === 1) {
            $this->mesActual = 12;
            $this->anioActual--;
        } else {
            $this->mesActual--;
        }
        $this->cargarDatos();
    }

    public function mesSiguiente(): void
    {
        if ($this->mesActual === 12) {
            $this->mesActual = 1;
            $this->anioActual++;
        } else {
            $this->mesActual++;
        }
        $this->cargarDatos();
    }

    public function irAHoy(): void
    {
        $hoy = Carbon::now();
        $this->mesActual  = $hoy->month;
        $this->anioActual = $hoy->year;
        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Navigation — año (vista anual)                                       */
    /* ────────────────────────────────────────────────────────────────────── */

    public function irAnioAnterior(): void
    {
        $this->anioActual--;
        $this->cargarDatos();
    }

    public function irAnioSiguiente(): void
    {
        $this->anioActual++;
        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Modo planificación                                                   */
    /* ────────────────────────────────────────────────────────────────────── */

    public function toggleModoPlaneacion(): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->modoPlaneacion = ! $this->modoPlaneacion;
        if (! $this->modoPlaneacion) {
            $this->busquedaSidebar = '';
        }
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Modal CRUD — vista mensual                                           */
    /* ────────────────────────────────────────────────────────────────────── */

    public function abrirModalPlanificacion(int $dia): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $this->fechaInicioPlan = Carbon::createFromDate($this->anioActual, $this->mesActual, $dia)->toDateString();
        $this->fechaFinPlan    = $this->fechaInicioPlan;
        $this->mostrarModalPlanificacion = true;
    }

    public function abrirModalPlanificacionRango(int $diaInicio, int $diaFin): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();

        $a = Carbon::createFromDate($this->anioActual, $this->mesActual, $diaInicio);
        $b = Carbon::createFromDate($this->anioActual, $this->mesActual, $diaFin);

        $this->fechaInicioPlan = $a->min($b)->toDateString();
        $this->fechaFinPlan    = $a->max($b)->toDateString();
        $this->mostrarModalPlanificacion = true;
    }

    public function abrirModalConCurso(int $cursoId): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $this->cursoId = $cursoId;
        $hoy       = Carbon::today();
        $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1);
        if ($hoy->month === $this->mesActual && $hoy->year === $this->anioActual) {
            $this->fechaInicioPlan = $hoy->toDateString();
            $this->fechaFinPlan    = $hoy->toDateString();
        } else {
            $this->fechaInicioPlan = $primerDia->toDateString();
            $this->fechaFinPlan    = $primerDia->toDateString();
        }
        $this->mostrarModalPlanificacion = true;
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Modal CRUD — vista anual                                             */
    /* ────────────────────────────────────────────────────────────────────── */

    public function abrirModalAnualSemana(int $semana): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $semana = max(1, min($semana, count($this->semanasDelAnio)));
        $this->semanaInicioPlan = $semana;
        $this->semanaFinPlan    = $semana;
        // Pre-fill fechas para validación
        $this->sincronizarFechasDesdeSemanas();
        $this->mostrarModalPlanificacion = true;
    }

    public function abrirModalAnualRango(int $semanaInicio, int $semanaFin): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $total = count($this->semanasDelAnio);
        $this->semanaInicioPlan = max(1, min($semanaInicio, $total));
        $this->semanaFinPlan    = max(1, min($semanaFin, $total));
        if ($this->semanaInicioPlan > $this->semanaFinPlan) {
            [$this->semanaInicioPlan, $this->semanaFinPlan] = [$this->semanaFinPlan, $this->semanaInicioPlan];
        }
        $this->sincronizarFechasDesdeSemanas();
        $this->mostrarModalPlanificacion = true;
    }

    public function updatedSemanaInicioPlan(): void
    {
        if ($this->modoVista === 'anual') {
            // Asegurar que semanaFin >= semanaInicio
            if ($this->semanaFinPlan < $this->semanaInicioPlan) {
                $this->semanaFinPlan = $this->semanaInicioPlan;
            }
            $this->sincronizarFechasDesdeSemanas();
        }
    }

    public function updatedSemanaFinPlan(): void
    {
        if ($this->modoVista === 'anual') {
            $this->sincronizarFechasDesdeSemanas();
        }
    }

    private function sincronizarFechasDesdeSemanas(): void
    {
        $semanas = $this->semanasDelAnio;
        if (empty($semanas)) {
            return;
        }
        $semInicio = collect($semanas)->firstWhere('numero', $this->semanaInicioPlan);
        $semFin    = collect($semanas)->firstWhere('numero', $this->semanaFinPlan);
        if ($semInicio) {
            $this->fechaInicioPlan = $semInicio['inicio'];
        }
        if ($semFin) {
            $this->fechaFinPlan = $semFin['fin'];
        }
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Modal CRUD — compartido                                              */
    /* ────────────────────────────────────────────────────────────────────── */

    public function seleccionarCurso(int $id): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->cursoId = $id;
    }

    public function editarPlanificacion(int $id): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $plan = PlanificacionCurso::findOrFail($id);

        $this->editandoId      = $plan->id;
        $this->cursoId         = $plan->curso_id;
        $this->fechaInicioPlan = $plan->fecha_inicio->toDateString();
        $this->fechaFinPlan    = $plan->fecha_fin->toDateString();
        $this->notas           = $plan->notas ?? '';

        // Calcular semanas para los selects en vista anual
        if ($this->modoVista === 'anual' && ! empty($this->semanasDelAnio)) {
            $this->semanaInicioPlan = $this->semanaParaFecha($plan->fecha_inicio) ?? 1;
            $this->semanaFinPlan    = $this->semanaParaFecha($plan->fecha_fin)    ?? 1;
        }

        $this->mostrarModalPlanificacion = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModalPlanificacion = false;
        $this->resetValidation();
        $this->resetModal();
    }

    public function guardarPlanificacion(): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        // En vista anual: convertir semanas → fechas antes de validar
        if ($this->modoVista === 'anual') {
            $this->sincronizarFechasDesdeSemanas();
        }

        $this->validate([
            'cursoId'         => 'required|integer|exists:cursos,id',
            'fechaInicioPlan' => 'required|date',
            'fechaFinPlan'    => 'required|date|after_or_equal:fechaInicioPlan',
        ]);

        if ($this->editandoId) {
            PlanificacionCurso::whereKey($this->editandoId)->update([
                'curso_id'     => $this->cursoId,
                'fecha_inicio' => $this->fechaInicioPlan,
                'fecha_fin'    => $this->fechaFinPlan,
                'notas'        => $this->notas ?: null,
            ]);
        } else {
            PlanificacionCurso::create([
                'curso_id'     => $this->cursoId,
                'fecha_inicio' => $this->fechaInicioPlan,
                'fecha_fin'    => $this->fechaFinPlan,
                'notas'        => $this->notas ?: null,
            ]);
        }

        $this->cerrarModal();
        $this->cargarDatos();
    }

    public function borrarPlanificacion(int $id): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        PlanificacionCurso::whereKey($id)->delete();
        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Drag/resize — vista mensual (sin cambios)                            */
    /* ────────────────────────────────────────────────────────────────────── */

    public function ajustarBordePlanificacion(int $id, string $borde, int $dia): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        if (! in_array($borde, ['inicio', 'fin'], true)) {
            return;
        }

        $dia = max(1, min($dia, $this->diasEnMes));
        $fechaObjetivo = Carbon::createFromDate($this->anioActual, $this->mesActual, $dia)->startOfDay();

        $plan = PlanificacionCurso::findOrFail($id);

        if ($borde === 'inicio') {
            $nuevaFechaInicio = $fechaObjetivo->copy()->min($plan->fecha_fin->copy());
            $plan->update(['fecha_inicio' => $nuevaFechaInicio->toDateString()]);
        } else {
            $nuevaFechaFin = $fechaObjetivo->copy()->max($plan->fecha_inicio->copy());
            $plan->update(['fecha_fin' => $nuevaFechaFin->toDateString()]);
        }

        $this->cargarDatos();
    }

    public function moverPlanificacion(int $id, int $nuevoDiaInicio): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        $plan = PlanificacionCurso::findOrFail($id);

        $duracionDias = $plan->fecha_inicio->diffInDays($plan->fecha_fin);

        $nuevoDiaInicio = max(1, min($nuevoDiaInicio, $this->diasEnMes));
        $nuevoDiaFin    = $nuevoDiaInicio + $duracionDias;

        if ($nuevoDiaFin > $this->diasEnMes) {
            $nuevoDiaFin    = $this->diasEnMes;
            $nuevoDiaInicio = max(1, $nuevoDiaFin - $duracionDias);
        }

        $plan->update([
            'fecha_inicio' => Carbon::createFromDate($this->anioActual, $this->mesActual, $nuevoDiaInicio)->toDateString(),
            'fecha_fin'    => Carbon::createFromDate($this->anioActual, $this->mesActual, $nuevoDiaFin)->toDateString(),
        ]);

        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Drag/resize — vista anual                                            */
    /* ────────────────────────────────────────────────────────────────────── */

    public function moverPlanificacionSemanas(int $id, int $nuevaSemana): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        $plan  = PlanificacionCurso::findOrFail($id);
        $total = count($this->semanasDelAnio);

        $semIni = $this->semanaParaFecha($plan->fecha_inicio) ?? 1;
        $semFin = $this->semanaParaFecha($plan->fecha_fin)    ?? 1;
        $duracion = max(0, $semFin - $semIni);

        $nuevaSemana  = max(1, min($nuevaSemana, $total));
        $nuevaSemFin  = $nuevaSemana + $duracion;

        if ($nuevaSemFin > $total) {
            $nuevaSemFin = $total;
            $nuevaSemana = max(1, $nuevaSemFin - $duracion);
        }

        $semanas = $this->semanasDelAnio;
        $datoIni = $semanas[$nuevaSemana - 1]  ?? null;
        $datoFin = $semanas[$nuevaSemFin - 1]  ?? null;

        if (! $datoIni || ! $datoFin) {
            return;
        }

        $plan->update([
            'fecha_inicio' => $datoIni['inicio'],
            'fecha_fin'    => $datoFin['fin'],
        ]);

        $this->cargarDatos();
    }

    public function ajustarBordePlanificacionSemana(int $id, string $borde, int $semana): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        if (! in_array($borde, ['inicio', 'fin'], true)) {
            return;
        }

        $total   = count($this->semanasDelAnio);
        $semana  = max(1, min($semana, $total));
        $semanas = $this->semanasDelAnio;
        $plan    = PlanificacionCurso::findOrFail($id);

        if ($borde === 'inicio') {
            $semActualFin = $this->semanaParaFecha($plan->fecha_fin) ?? $total;
            $semana       = min($semana, $semActualFin);
            $plan->update(['fecha_inicio' => $semanas[$semana - 1]['inicio']]);
        } else {
            $semActualIni = $this->semanaParaFecha($plan->fecha_inicio) ?? 1;
            $semana       = max($semana, $semActualIni);
            $plan->update(['fecha_fin' => $semanas[$semana - 1]['fin']]);
        }

        $this->cargarDatos();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Data loading                                                         */
    /* ────────────────────────────────────────────────────────────────────── */

    private function cargarDatos(): void
    {
        if ($this->modoVista === 'anual') {
            $this->cargarDatosAnuales();
        } else {
            $this->cargarSemanasDelMes();
        }
    }

    private function resetModal(): void
    {
        $this->editandoId      = null;
        $this->cursoId         = null;
        $this->fechaInicioPlan = '';
        $this->fechaFinPlan    = '';
        $this->notas           = '';
        $this->queryModal      = '';
        $this->semanaInicioPlan = 1;
        $this->semanaFinPlan    = 1;
    }

    private function cargarCursosDisponibles(): void
    {
        if ($this->modoVista === 'anual') {
            // En vista anual: cursos planificados en el año actual
            $inicioAnio = Carbon::create($this->anioActual, 1, 1)->startOfDay();
            $finAnio    = Carbon::create($this->anioActual, 12, 31)->endOfDay();

            $planificadosIds = PlanificacionCurso::where('fecha_inicio', '<=', $finAnio)
                ->where('fecha_fin', '>=', $inicioAnio)
                ->pluck('curso_id')
                ->unique()
                ->all();
        } else {
            $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
            $ultimoDia = $primerDia->copy()->endOfMonth()->startOfDay();

            $planificadosIds = PlanificacionCurso::where('fecha_inicio', '<=', $ultimoDia)
                ->where('fecha_fin', '>=', $primerDia)
                ->pluck('curso_id')
                ->unique()
                ->all();
        }

        $todos = Curso::orderBy('titulo')
            ->get(['id', 'titulo'])
            ->map(fn ($c) => [
                'id'     => $c->id,
                'titulo' => $c->titulo,
                'bg'     => self::PALETTE[$c->id % count(self::PALETTE)],
            ]);

        $this->cursosDisponibles   = $todos->all();
        $this->cursosSinPlanificar = $todos
            ->filter(fn ($c) => ! in_array($c['id'], $planificadosIds, true))
            ->values()
            ->all();
    }

    private function obtenerPlanificaciones(Carbon $desde, Carbon $hasta)
    {
        $query = PlanificacionCurso::with('curso:id,titulo,capacitador_id')
            ->where('fecha_inicio', '<=', $hasta)
            ->where('fecha_fin', '>=', $desde);

        $user = Auth::user();

        if ($user->isCapacitador() && ! $user->hasAdminAccess()) {
            $query->whereHas('curso', fn ($q) => $q->where('capacitador_id', $user->id));
        } elseif (! $user->hasAdminAccess()) {
            $estamentoId = $user->estamento_id;
            $query->whereHas(
                'curso.estamentos',
                fn ($q) => $q->where('estamentos.id', $estamentoId)
            );
        }

        return $query->orderBy('fecha_inicio')->get();
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Vista ANUAL                                                          */
    /* ────────────────────────────────────────────────────────────────────── */

    private function cargarDatosAnuales(): void
    {
        $inicioAnio = Carbon::create($this->anioActual, 1, 1)->startOfDay();
        $finAnio    = Carbon::create($this->anioActual, 12, 31)->endOfDay();

        $planificaciones = $this->obtenerPlanificaciones($inicioAnio, $finAnio);

        $semanasBruto = $this->generarSemanasAnio($this->anioActual);
        $hoy          = Carbon::today();

        // Construir mapa: número_semana → lista de cursos
        $cursosPorSemana = [];
        foreach ($planificaciones as $plan) {
            $sIni = $this->semanaParaFecha($plan->fecha_inicio, $semanasBruto);
            $sFin = $this->semanaParaFecha($plan->fecha_fin,    $semanasBruto);

            if ($sIni === null || $sFin === null) {
                continue;
            }

            $colorIdx = $plan->curso_id % count(self::PALETTE);

            for ($s = $sIni; $s <= $sFin; $s++) {
                $cursosPorSemana[$s][] = [
                    'id'       => $plan->id,
                    'curso_id' => $plan->curso_id,
                    'titulo'   => $plan->curso->titulo ?? '—',
                    'bg'       => self::PALETTE[$colorIdx],
                    'esInicio' => $s === $sIni,
                    'esFin'    => $s === $sFin,
                    'notas'    => $plan->notas,
                ];
            }
        }

        // Construir array de semanas con datos
        $semanas = [];
        foreach ($semanasBruto as $sem) {
            $fechaIni = Carbon::parse($sem['inicio']);
            $fechaFin = Carbon::parse($sem['fin']);
            $cursos   = $cursosPorSemana[$sem['numero']] ?? [];

            // Desduplicar: si el mismo plan_id aparece (no debería), limpiarlo
            $planIds = array_column($cursos, 'id');
            $planIdsUnicos = array_unique($planIds);
            if (count($planIdsUnicos) < count($planIds)) {
                $vistos  = [];
                $cursos  = array_filter($cursos, function ($c) use (&$vistos) {
                    if (isset($vistos[$c['id']])) return false;
                    $vistos[$c['id']] = true;
                    return true;
                });
                $cursos = array_values($cursos);
            }

            $semanas[] = [
                'numero'    => $sem['numero'],
                'inicio'    => $sem['inicio'],
                'fin'       => $sem['fin'],
                'mes'       => $sem['mes'],
                'esHoy'     => $fechaIni->lte($hoy) && $fechaFin->gte($hoy),
                'esPasada'  => $fechaFin->lt($hoy),
                'cursos'    => $cursos,
                'conflicto' => count(array_unique(array_column($cursos, 'id'))) >= 2,
            ];
        }

        $this->semanasDelAnio = $semanas;

        // Construir grupos de meses para header
        $this->mesesDelAnio = $this->calcularMesesDelAnio($semanas);
    }

    /**
     * Genera todas las semanas del año: semana 1 = semana que contiene el primer lunes del año.
     * Cada semana va de lunes a domingo.
     *
     * @return array<int, array{numero: int, inicio: string, fin: string, mes: int}>
     */
    private function generarSemanasAnio(int $anio): array
    {
        // Buscar el primer lunes en o antes del 1 de enero
        $cursor  = Carbon::create($anio, 1, 1)->startOfWeek(Carbon::MONDAY);
        $semanas = [];
        $numero  = 1;

        while (true) {
            $fin = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

            // Si el lunes cae en el año anterior, saltar (solo si no hay días en el año actual)
            if ($fin->year < $anio) {
                $cursor->addWeek();
                continue;
            }

            // Parar cuando el lunes ya esté en el año siguiente
            if ($cursor->year > $anio) {
                break;
            }

            $semanas[] = [
                'numero' => $numero++,
                'inicio' => $cursor->toDateString(),
                'fin'    => $fin->toDateString(),
                'mes'    => $this->mesDelaSemana($cursor->copy(), $fin->copy()),
            ];

            $cursor->addWeek();
        }

        return $semanas;
    }

    /**
     * Devuelve el número de mes (1-12) al que pertenece la semana
     * basándose en qué mes tiene más días en esa semana (≥4 de 7).
     */
    private function mesDelaSemana(Carbon $lunes, Carbon $domingo): int
    {
        $counts = [];
        $d = $lunes->copy();
        for ($i = 0; $i < 7; $i++) {
            $counts[$d->month] = ($counts[$d->month] ?? 0) + 1;
            $d->addDay();
        }
        arsort($counts);
        return array_key_first($counts);
    }

    /**
     * Agrupa las semanas por mes y calcula el span (número de semanas) de cada mes.
     *
     * @param  array $semanas  Output de cargarDatosAnuales
     * @return array<int, array{nombre: string, span: int, semanaInicio: int, semanaFin: int}>
     */
    private function calcularMesesDelAnio(array $semanas): array
    {
        $nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                         'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $meses  = [];
        $ultimo = null;

        foreach ($semanas as $sem) {
            $mes = $sem['mes'];
            if ($mes !== $ultimo) {
                $meses[] = [
                    'nombre'       => $nombresMeses[$mes - 1],
                    'span'         => 1,
                    'semanaInicio' => $sem['numero'],
                    'semanaFin'    => $sem['numero'],
                ];
                $ultimo = $mes;
            } else {
                $meses[count($meses) - 1]['span']++;
                $meses[count($meses) - 1]['semanaFin'] = $sem['numero'];
            }
        }

        return $meses;
    }

    /**
     * Encuentra el número de semana del año que contiene a $fecha.
     * Utiliza el array $semanasDelAnio (o el generado ad-hoc si se pasa $semanas).
     */
    private function semanaParaFecha(Carbon $fecha, array $semanas = []): ?int
    {
        if (empty($semanas)) {
            $semanas = $this->semanasDelAnio;
        }
        foreach ($semanas as $sem) {
            if ($fecha->between(
                Carbon::parse($sem['inicio'])->startOfDay(),
                Carbon::parse($sem['fin'])->endOfDay()
            )) {
                return $sem['numero'];
            }
        }
        // Fuera del año: devolver extremo más cercano
        if (! empty($semanas)) {
            $primera = Carbon::parse($semanas[0]['inicio']);
            $ultima  = Carbon::parse($semanas[count($semanas) - 1]['fin']);
            if ($fecha->lt($primera)) return $semanas[0]['numero'];
            if ($fecha->gt($ultima))  return $semanas[count($semanas) - 1]['numero'];
        }
        return null;
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Vista MENSUAL (sin cambios funcionales)                              */
    /* ────────────────────────────────────────────────────────────────────── */

    private function cargarSemanasDelMes(): void
    {
        $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1);
        $this->diasEnMes = $primerDia->daysInMonth;
        $hoy = Carbon::today();

        $inicioGrid = $primerDia->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid    = $primerDia->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $primerDiaCal = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
        $ultimoDiaCal = $primerDiaCal->copy()->endOfMonth()->startOfDay();
        $planificaciones = $this->obtenerPlanificaciones($primerDiaCal, $ultimoDiaCal);

        $globalSlots  = [];
        $slotOcupados = [];
        foreach ($planificaciones as $plan) {
            $slot = 0;
            while (true) {
                $conflict = false;
                if (isset($slotOcupados[$slot])) {
                    foreach ($slotOcupados[$slot] as [$oStart, $oEnd]) {
                        if ($plan->fecha_inicio->lte($oEnd) && $plan->fecha_fin->gte($oStart)) {
                            $conflict = true;
                            break;
                        }
                    }
                }
                if (! $conflict) break;
                $slot++;
            }
            $globalSlots[$plan->id] = $slot;
            $slotOcupados[$slot][]  = [$plan->fecha_inicio, $plan->fecha_fin];
        }

        $semanas = [];
        $cursor  = $inicioGrid->copy();

        while ($cursor->lte($finGrid)) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

            $dias = [];
            $d    = $weekStart->copy();
            for ($i = 0; $i < 7; $i++) {
                $dias[] = [
                    'num'         => $d->day,
                    'esHoy'       => $d->isSameDay($hoy),
                    'esMesActual' => $d->month === $this->mesActual && $d->year === $this->anioActual,
                    'fecha'       => $d->toDateString(),
                    'esWeekend'   => $d->dayOfWeekIso >= 6,
                ];
                $d->addDay();
            }

            $barras = [];
            foreach ($planificaciones as $plan) {
                if ($plan->fecha_fin->lt($weekStart) || $plan->fecha_inicio->gt($weekEnd)) {
                    continue;
                }

                $barStart = $plan->fecha_inicio->lt($weekStart) ? $weekStart->copy() : $plan->fecha_inicio->copy();
                $barEnd   = $plan->fecha_fin->gt($weekEnd)      ? $weekEnd->copy()   : $plan->fecha_fin->copy();

                $col  = $barStart->dayOfWeekIso;
                $span = (int) $barStart->diffInDays($barEnd) + 1;

                $colorIdx = $plan->curso_id % count(self::PALETTE);

                $edgeStartDay = ($barStart->month === $this->mesActual && $barStart->year === $this->anioActual)
                    ? $barStart->day : 1;
                $edgeEndDay = ($barEnd->month === $this->mesActual && $barEnd->year === $this->anioActual)
                    ? $barEnd->day : $this->diasEnMes;

                $primerDiaMes  = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
                $ultimoDiaMes  = $primerDiaMes->copy()->endOfMonth()->startOfDay();
                $extiendePorIzq = $plan->fecha_inicio->lt($primerDiaMes);
                $extiendePorDer = $plan->fecha_fin->gt($ultimoDiaMes);

                $barras[] = [
                    'id'             => $plan->id,
                    'titulo'         => $plan->curso->titulo ?? '—',
                    'col'            => $col,
                    'span'           => $span,
                    'slot'           => $globalSlots[$plan->id],
                    'bg'             => self::PALETTE[$colorIdx],
                    'roundLeft'      => $plan->fecha_inicio->gte($weekStart),
                    'roundRight'     => $plan->fecha_fin->lte($weekEnd),
                    'notas'          => $plan->notas,
                    'fechaIni'       => $plan->fecha_inicio->toDateString(),
                    'fechaFin'       => $plan->fecha_fin->toDateString(),
                    'edgeStartDay'   => $edgeStartDay,
                    'edgeEndDay'     => $edgeEndDay,
                    'segStartDay'    => $edgeStartDay,
                    'extiendePorIzq' => $extiendePorIzq,
                    'extiendePorDer' => $extiendePorDer,
                ];
            }

            $maxSlot = 0;
            foreach ($barras as $b) {
                if ($b['slot'] > $maxSlot) $maxSlot = $b['slot'];
            }

            $semanas[] = [
                'dias'    => $dias,
                'barras'  => $barras,
                'maxSlot' => count($barras) ? $maxSlot + 1 : 0,
            ];

            $cursor->addWeek();
        }

        $this->semanasDelMes = $semanas;
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Render                                                               */
    /* ────────────────────────────────────────────────────────────────────── */

    public function render()
    {
        $this->cargarCursosDisponibles();

        $hoy         = Carbon::now();
        $esMesActual = $this->mesActual === $hoy->month && $this->anioActual === $hoy->year;
        $esAnioActual = $this->anioActual === $hoy->year;

        $busqueda    = mb_strtolower(trim($this->busquedaSidebar));
        $queryMod    = mb_strtolower(trim($this->queryModal));

        $sidebarList = $busqueda
            ? array_values(array_filter($this->cursosSinPlanificar,
                fn ($c) => str_contains(mb_strtolower($c['titulo']), $busqueda)))
            : $this->cursosSinPlanificar;

        $modalList   = $queryMod
            ? array_values(array_filter($this->cursosDisponibles,
                fn ($c) => str_contains(mb_strtolower($c['titulo']), $queryMod)))
            : $this->cursosDisponibles;

        return view('livewire.capacitador.calendario-capacitaciones', [
            'esAdmin'             => Auth::user()->hasAdminAccess(),
            'esMesActual'         => $esMesActual,
            'esAnioActual'        => $esAnioActual,
            'cursosDisponibles'   => $this->cursosDisponibles,
            'cursosSinPlanificar' => $this->cursosSinPlanificar,
            'sidebarList'         => $sidebarList,
            'modalList'           => $modalList,
            'nSemanas'            => count($this->semanasDelAnio),
        ])
            ->extends('layouts.panel')
            ->section('content');
    }
}
