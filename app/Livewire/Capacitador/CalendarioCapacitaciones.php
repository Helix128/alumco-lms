<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\PlanificacionCurso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalendarioCapacitaciones extends Component
{
    public int $mesActual;
    public int $anioActual;

    public bool $modoPlaneacion = false;
    public bool $mostrarModalPlanificacion = false;

    public ?int $editandoId = null;
    public ?int $cursoId = null;
    public string $fechaInicioPlan = '';
    public string $fechaFinPlan = '';
    public string $notas = '';

    /** @var array Weeks with day cells + positioned bars for calendar view */
    public array $semanasDelMes = [];

    /** @var int Total days in the current month */
    public int $diasEnMes = 30;

    public array $cursosDisponibles    = [];
    public array $cursosSinPlanificar  = [];

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
    /*  Navigation                                                           */
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

    public function toggleModoPlaneacion(): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $this->modoPlaneacion = ! $this->modoPlaneacion;
    }

    /* ────────────────────────────────────────────────────────────────────── */
    /*  Modal CRUD                                                           */
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
        // Default to today if within the shown month, else first day of month
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

    public function editarPlanificacion(int $id): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);
        $plan = PlanificacionCurso::findOrFail($id);

        $this->editandoId      = $plan->id;
        $this->cursoId         = $plan->curso_id;
        $this->fechaInicioPlan = $plan->fecha_inicio->toDateString();
        $this->fechaFinPlan    = $plan->fecha_fin->toDateString();
        $this->notas           = $plan->notas ?? '';
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

    /**
     * Move an entire planificacion by shifting both dates together.
     * The duration is preserved; clamps so the period stays within the displayed month.
     */
    public function moverPlanificacion(int $id, int $nuevoDiaInicio): void
    {
        abort_unless(Auth::user()->hasAdminAccess(), 403);

        $plan = PlanificacionCurso::findOrFail($id);

        $duracionDias = $plan->fecha_inicio->diffInDays($plan->fecha_fin);

        $nuevoDiaInicio = max(1, min($nuevoDiaInicio, $this->diasEnMes));
        $nuevoDiaFin    = $nuevoDiaInicio + $duracionDias;

        // If end goes past month, slide start back
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
    /*  Data loading                                                         */
    /* ────────────────────────────────────────────────────────────────────── */

    private function cargarDatos(): void
    {
        $this->cargarSemanasDelMes();
    }

    private function resetModal(): void
    {
        $this->editandoId      = null;
        $this->cursoId         = null;
        $this->fechaInicioPlan = '';
        $this->fechaFinPlan    = '';
        $this->notas           = '';
    }

    private function cargarCursosDisponibles(): void
    {
        $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
        $ultimoDia = $primerDia->copy()->endOfMonth()->startOfDay();

        $planificadosIds = PlanificacionCurso::where('fecha_inicio', '<=', $ultimoDia)
            ->where('fecha_fin', '>=', $primerDia)
            ->pluck('curso_id')
            ->unique()
            ->all();

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

    private function obtenerPlanificaciones()
    {
        $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
        $ultimoDia = $primerDia->copy()->endOfMonth()->startOfDay();

        $query = PlanificacionCurso::with('curso:id,titulo,capacitador_id')
            ->where('fecha_inicio', '<=', $ultimoDia)
            ->where('fecha_fin', '>=', $primerDia);

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

    private function cargarSemanasDelMes(): void
    {
        $primerDia = Carbon::createFromDate($this->anioActual, $this->mesActual, 1);
        $this->diasEnMes = $primerDia->daysInMonth;
        $hoy = Carbon::today();

        $inicioGrid = $primerDia->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid    = $primerDia->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $planificaciones = $this->obtenerPlanificaciones();

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

                $primerDiaMes  = $primerDia->copy()->startOfDay();
                $ultimoDiaMes  = $primerDia->copy()->endOfMonth()->startOfDay();
                $extiendePorIzq = $plan->fecha_inicio->lt($primerDiaMes);
                $extiendePorDer = $plan->fecha_fin->gt($ultimoDiaMes);

                $barras[] = [
                    'id'             => $plan->id,
                    'titulo'         => $plan->curso->titulo ?? "\xe2\x80\x94",
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

    public function render()
    {
        $this->cargarCursosDisponibles();

        $hoy         = Carbon::now();
        $esMesActual = $this->mesActual === $hoy->month && $this->anioActual === $hoy->year;

        return view('livewire.capacitador.calendario-capacitaciones', [
            'esAdmin'             => Auth::user()->hasAdminAccess(),
            'esMesActual'         => $esMesActual,
            'cursosDisponibles'   => $this->cursosDisponibles,
            'cursosSinPlanificar' => $this->cursosSinPlanificar,
        ])
            ->extends('layouts.panel')
            ->section('content');
    }
}
