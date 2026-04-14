<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalendarioCapacitaciones extends Component
{
    // ── Vista ──────────────────────────────────────────────────────────────
    public string $modoVista = 'anual';      // 'anual' | 'mensual'
    public bool   $readonly  = false;

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
    public ?int $sedeIdPlan        = null;

    // ── Modal en vista anual ───────────────────────────────────────────────
    public int $semanaInicioPlan = 1;
    public int $semanaFinPlan    = 1;

    // ── Copiar año ────────────────────────────────────────────────────────
    public bool $mostrarModalCopiarAnio = false;
    public int  $anioDestino;

    // ── Filtro de sede ────────────────────────────────────────────────────
    public ?int $filtroSedeId = null;
    public array $sedes = [];

    // ── Datos vista mensual ────────────────────────────────────────────────
    /** @var array Weeks with day cells + positioned bars for calendar view */
    public array $semanasDelMes = [];
    public int $diasEnMes = 30;

    // ── Datos vista anual ──────────────────────────────────────────────────
    /** @var array Semanas con sus cursos planificados */
    public array $semanasDelAnio = [];
    /** @var array Una fila por sede con sus cursos indexados por semana */
    public array $filasAnuales   = [];
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
        $this->mesActual    = Carbon::now()->month;
        $this->anioActual   = Carbon::now()->year;
        $this->anioDestino  = $this->anioActual + 1;
        $this->readonly     = request()->routeIs('calendario-cursos.index');
        $this->sedes        = Sede::orderBy('nombre')->get(['id', 'nombre'])->toArray();
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
        // El modo planificación es exclusivo de la vista anual
        if ($modo === 'mensual' && $this->modoPlaneacion) {
            $this->modoPlaneacion = false;
        }
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

    public function abrirModalAnualSemana(int $semana, $sedeId = null): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $semana = max(1, min($semana, count($this->semanasDelAnio)));
        $this->semanaInicioPlan = $semana;
        $this->semanaFinPlan    = $semana;
        $this->sedeIdPlan       = $sedeId ? (int) $sedeId : null;
        // Pre-fill fechas para validación
        $this->sincronizarFechasDesdeSemanas();
        $this->mostrarModalPlanificacion = true;
    }

    public function abrirModalAnualRango(int $semanaInicio, int $semanaFin, $sedeId = null): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->resetModal();
        $total = count($this->semanasDelAnio);
        $this->semanaInicioPlan = max(1, min($semanaInicio, $total));
        $this->semanaFinPlan    = max(1, min($semanaFin, $total));
        if ($this->semanaInicioPlan > $this->semanaFinPlan) {
            [$this->semanaInicioPlan, $this->semanaFinPlan] = [$this->semanaFinPlan, $this->semanaInicioPlan];
        }
        $this->sedeIdPlan = $sedeId ? (int) $sedeId : null;
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
        $this->sedeIdPlan      = $plan->sede_id;

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
            'sedeIdPlan'      => 'nullable|integer|exists:sedes,id',
        ]);

        $datos = [
            'curso_id'     => $this->cursoId,
            'sede_id'      => $this->sedeIdPlan,
            'fecha_inicio' => $this->fechaInicioPlan,
            'fecha_fin'    => $this->fechaFinPlan,
            'notas'        => $this->notas ?: null,
        ];

        if ($this->editandoId) {
            PlanificacionCurso::whereKey($this->editandoId)->update($datos);
        } else {
            PlanificacionCurso::create($datos);
        }

        $this->cerrarModal();
        $this->filtroSedeId = null; // Mostrar todas las sedes para que el ítem guardado sea visible
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

    public function moverPlanificacionSemanas(int $id, int $nuevaSemana, int $targetSedeId = -1): void
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

        $updates = [
            'fecha_inicio' => $datoIni['inicio'],
            'fecha_fin'    => $datoFin['fin'],
        ];

        // targetSedeId: -1 = no cambiar, 0 = todas las sedes (null), >0 = sede específica
        if ($targetSedeId !== -1) {
            $nuevaSedeId = $targetSedeId === 0 ? null : $targetSedeId;
            if ($nuevaSedeId !== null) {
                // Validar que la sede existe en la lista actual del componente
                $sedeIds = array_column($this->sedes, 'id');
                if (! in_array($nuevaSedeId, $sedeIds, true)) {
                    return;
                }
            }
            $updates['sede_id'] = $nuevaSedeId;
        }

        $plan->update($updates);

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
        $this->sedeIdPlan      = null;
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

        $user  = Auth::user();
        $query = Curso::orderBy('titulo');

        if ($user->isCapacitador() && ! $user->hasAdminAccess()) {
            $query->where('capacitador_id', $user->id);
        }

        $todos = $query
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
        $query = PlanificacionCurso::with('curso:id,titulo,capacitador_id', 'sede:id,nombre')
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
            // Colaboradores: solo su sede + planificaciones globales
            if ($user->sede_id) {
                $query->where(fn ($q) => $q->whereNull('sede_id')->orWhere('sede_id', $user->sede_id));
            }
        }

        // Filtro global de sede (header dropdown)
        if ($this->filtroSedeId) {
            $query->where(fn ($q) => $q->whereNull('sede_id')->orWhere('sede_id', $this->filtroSedeId));
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
        $semanasBruto    = $this->generarSemanasAnio($this->anioActual);
        $hoy             = Carbon::today();

        // ── Metadata de semanas (sin cursos) — para cabeceras y selectores ──
        $semanasBase = [];
        foreach ($semanasBruto as $sem) {
            $fechaIni      = Carbon::parse($sem['inicio']);
            $fechaFin      = Carbon::parse($sem['fin']);
            $semanasBase[] = [
                'numero'   => $sem['numero'],
                'inicio'   => $sem['inicio'],
                'fin'      => $sem['fin'],
                'mes'      => $sem['mes'],
                'esHoy'    => $fechaIni->lte($hoy) && $fechaFin->gte($hoy),
                'esPasada' => $fechaFin->lt($hoy),
            ];
        }

        $this->semanasDelAnio = $semanasBase;
        $this->mesesDelAnio   = $this->calcularMesesDelAnio($semanasBase);

        // ── Agrupar plans por sede_id ─────────────────────────────────────
        $plansPorSede = [];
        foreach ($planificaciones as $plan) {
            $key                = $plan->sede_id === null ? 'null' : (string) $plan->sede_id;
            $plansPorSede[$key][] = $plan;
        }

        // ── Una fila por sede: Global (null) + cada sede registrada ────────
        $listaSedes = array_merge(
            [['id' => null, 'nombre' => 'Todas las sedes']],
            array_map(fn ($s) => ['id' => $s['id'], 'nombre' => $s['nombre']], $this->sedes)
        );

        $filas = [];
        foreach ($listaSedes as $sedeInfo) {
            $sedeKey = $sedeInfo['id'] === null ? 'null' : (string) $sedeInfo['id'];
            $plans   = $plansPorSede[$sedeKey] ?? [];

            // Construir chips por semana para esta sede
            $cursosPorSemana = [];
            foreach ($plans as $plan) {
                $sIni = $this->semanaParaFecha($plan->fecha_inicio, $semanasBruto);
                $sFin = $this->semanaParaFecha($plan->fecha_fin,    $semanasBruto);
                if ($sIni === null || $sFin === null) {
                    continue;
                }
                $colorIdx = $plan->curso_id % count(self::PALETTE);
                for ($s = $sIni; $s <= $sFin; $s++) {
                    $cursosPorSemana[$s][] = [
                        'id'          => $plan->id,
                        'curso_id'    => $plan->curso_id,
                        'titulo'      => $plan->curso->titulo ?? '—',
                        'bg'          => self::PALETTE[$colorIdx],
                        'esInicio'    => $s === $sIni,
                        'esFin'       => $s === $sFin,
                        'notas'       => $plan->notas,
                        'sede_id'     => $plan->sede_id,
                        'sede_nombre' => $plan->sede->nombre ?? null,
                        'semaInicio'  => $sIni,
                        'semaFin'     => $sFin,
                    ];
                }
            }

            // Indexar por número de semana
            $semanasFila = [];
            foreach ($semanasBase as $sem) {
                $cursos                      = $cursosPorSemana[$sem['numero']] ?? [];
                $semanasFila[$sem['numero']] = [
                    'cursos'    => $cursos,
                    'conflicto' => $this->detectarConflictoSede($cursos),
                ];
            }

            $filas[] = [
                'sede_id' => $sedeInfo['id'],
                'nombre'  => $sedeInfo['nombre'],
                'semanas' => $semanasFila,
            ];
        }

        // ── Colisiones cruzadas: Todas las sedes ↔ cada sede específica ──────
        // Si en la misma semana "Todas las sedes" tiene cursos Y una sede específica
        // también tiene cursos, se trata de un solapamiento (usuarios de esa sede
        // tienen dos formaciones simultáneas).
        if (count($filas) > 1) {
            foreach ($semanasBase as $sem) {
                $semNum = $sem['numero'];
                $globalConCursos = count($filas[0]['semanas'][$semNum]['cursos']) > 0;
                if (! $globalConCursos) {
                    continue;
                }
                for ($i = 1; $i < count($filas); $i++) {
                    if (count($filas[$i]['semanas'][$semNum]['cursos']) > 0) {
                        $filas[0]['semanas'][$semNum]['conflicto'] = true;
                        $filas[$i]['semanas'][$semNum]['conflicto'] = true;
                    }
                }
            }
        }

        $this->filasAnuales = $filas;
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
                    'sede_id'        => $plan->sede_id,
                    'sede_nombre'    => $plan->sede->nombre ?? null,
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
    /*  Filtro de sede                                                       */
    /* ────────────────────────────────────────────────────────────────────── */

    public function filtrarPorSede($sedeId): void
    {
        $this->filtroSedeId = $sedeId ?: null;
        $this->cargarDatos();
    }

    /**
     * Detecta conflicto en una semana: dos planificaciones son conflicto
     * solo si comparten sede (o alguna es "todas las sedes" = null).
     */
    private function detectarConflictoSede(array $cursos): bool
    {
        $n = count($cursos);
        if ($n < 2) {
            return false;
        }

        // Deduplicar por plan id
        $unicos = [];
        foreach ($cursos as $c) {
            $unicos[$c['id']] = $c;
        }
        $unicos = array_values($unicos);

        for ($i = 0; $i < count($unicos); $i++) {
            for ($j = $i + 1; $j < count($unicos); $j++) {
                $sedeA = $unicos[$i]['sede_id'] ?? null;
                $sedeB = $unicos[$j]['sede_id'] ?? null;
                // Conflicto si: ambos null, uno null, o misma sede
                if ($sedeA === null || $sedeB === null || $sedeA === $sedeB) {
                    return true;
                }
            }
        }

        return false;
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Copiar planificación año a año                                       */
    /* ────────────────────────────────────────────────────────────────────── */

    public function abrirModalCopiarAnio(): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->anioDestino = $this->anioActual + 1;
        $this->mostrarModalCopiarAnio = true;
    }

    public function cerrarModalCopiarAnio(): void
    {
        $this->mostrarModalCopiarAnio = false;
        $this->resetValidation();
    }

    public function copiarAnio(): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        $this->validate([
            'anioDestino' => 'required|integer|min:2020|max:2099',
        ]);

        if ($this->anioDestino === $this->anioActual) {
            $this->addError('anioDestino', 'El año destino debe ser diferente al actual.');
            return;
        }

        $inicioDestino = Carbon::create($this->anioDestino, 1, 1)->startOfDay();
        $finDestino    = Carbon::create($this->anioDestino, 12, 31)->endOfDay();

        $existentes = PlanificacionCurso::where('fecha_inicio', '<=', $finDestino)
            ->where('fecha_fin', '>=', $inicioDestino)
            ->exists();

        if ($existentes) {
            $this->addError('anioDestino', "El año {$this->anioDestino} ya tiene planificaciones. Elimínalas primero si deseas reemplazarlas.");
            return;
        }

        // Usar overlap para capturar planes en semanas de borde (p. ej. semana 1 empieza
        // en dic del año anterior, semana 52/53 termina en ene del año siguiente).
        $semanasOrigen  = $this->generarSemanasAnio($this->anioActual);
        $semanasDestino = $this->generarSemanasAnio($this->anioDestino);
        $totalDestino   = count($semanasDestino);

        $limiteInfOrigen = Carbon::parse($semanasOrigen[0]['inicio'])->startOfDay();
        $limiteSuperOrigen = Carbon::parse($semanasOrigen[count($semanasOrigen) - 1]['fin'])->endOfDay();

        $planificaciones = PlanificacionCurso::where('fecha_inicio', '<=', $limiteSuperOrigen)
            ->where('fecha_fin', '>=', $limiteInfOrigen)
            ->get();

        if ($planificaciones->isEmpty()) {
            $this->addError('anioDestino', "No hay planificaciones en {$this->anioActual} para copiar.");
            return;
        }

        foreach ($planificaciones as $plan) {
            // Mapear a número de semana del año origen
            $semIni = $this->semanaParaFecha($plan->fecha_inicio, $semanasOrigen);
            $semFin = $this->semanaParaFecha($plan->fecha_fin,    $semanasOrigen);

            if ($semIni === null || $semFin === null) {
                continue;
            }

            // Ajustar al rango de semanas válido del año destino
            $semIni = max(1, min($semIni, $totalDestino));
            $semFin = max(1, min($semFin, $totalDestino));

            // Usar el lunes/domingo exactos de esa semana en el año destino
            $fechaIniDestino = $semanasDestino[$semIni - 1]['inicio'];
            $fechaFinDestino = $semanasDestino[$semFin - 1]['fin'];

            PlanificacionCurso::create([
                'curso_id'     => $plan->curso_id,
                'sede_id'      => $plan->sede_id,
                'fecha_inicio' => $fechaIniDestino,
                'fecha_fin'    => $fechaFinDestino,
                'notas'        => $plan->notas,
            ]);
        }

        $this->cerrarModalCopiarAnio();
        $this->anioActual = $this->anioDestino;
        $this->cargarDatos();
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

        $esAdmin = ! $this->readonly && Auth::user()->hasAdminAccess();

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

        $layout = $this->readonly ? 'layouts.user' : 'layouts.panel';

        return view('livewire.capacitador.calendario-capacitaciones', [
            'esAdmin'             => $esAdmin,
            'esMesActual'         => $esMesActual,
            'esAnioActual'        => $esAnioActual,
            'cursosDisponibles'   => $this->cursosDisponibles,
            'cursosSinPlanificar' => $this->cursosSinPlanificar,
            'sidebarList'         => $sidebarList,
            'modalList'           => $modalList,
            'nSemanas'            => count($this->semanasDelAnio),
            'filasAnuales'        => $this->filasAnuales,
            'readonly'            => $this->readonly,
            'userSedeId'          => Auth::user()->sede_id,
            'userSexo'            => Auth::user()->sexo ?? 'M',
        ])
            ->extends($layout)
            ->section('content');
    }
}
