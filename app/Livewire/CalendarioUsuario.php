<?php

namespace App\Livewire;

use App\Models\PlanificacionCurso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CalendarioUsuario extends Component
{
    public int $mesActual;

    public int $anioActual;

    /** @var array Grid mensual: semanas → días → cursos del día */
    public array $semanasDelMes = [];

    /** @var array Cursos con planificación activa o que inician en el mes actual */
    public array $cursosDelMes = [];

    /** @var array Cursos que inician en los próximos 60 días (fuera del mes actual) */
    public array $proximosCursos = [];

    private const PALETTE = [
        'bg-blue-500', 'bg-purple-600', 'bg-green-600', 'bg-orange-500', 'bg-rose-500',
        'bg-teal-500', 'bg-indigo-500', 'bg-amber-500', 'bg-cyan-600', 'bg-pink-500',
    ];

    private const PALETTE_BORDER = [
        'border-blue-500', 'border-purple-600', 'border-green-600', 'border-orange-500', 'border-rose-500',
        'border-teal-500', 'border-indigo-500', 'border-amber-500', 'border-cyan-600', 'border-pink-500',
    ];

    public function mount(): void
    {
        $this->mesActual = Carbon::now()->month;
        $this->anioActual = Carbon::now()->year;
        $this->cargarDatos();
    }

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
        $this->mesActual = $hoy->month;
        $this->anioActual = $hoy->year;
        $this->cargarDatos();
    }

    private function cargarDatos(): void
    {
        $user = Auth::user();

        $inicioMes = Carbon::createFromDate($this->anioActual, $this->mesActual, 1)->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth()->endOfDay();

        $planificaciones = PlanificacionCurso::with('curso:id,titulo', 'sede:id,nombre')
            ->where('fecha_inicio', '<=', $finMes)
            ->where('fecha_fin', '>=', $inicioMes)
            ->tap(fn (Builder $query) => $this->applyVisibility($query, $user))
            ->orderBy('fecha_inicio')
            ->get();

        $this->construirGrid($inicioMes, $finMes, $planificaciones);
        $this->construirListaMes($planificaciones, $inicioMes, $finMes);
        $this->construirProximos($user, $finMes);
    }

    private function construirGrid(Carbon $inicioMes, Carbon $finMes, $planificaciones): void
    {
        $hoy = Carbon::today();
        $inicioGrid = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid = $finMes->copy()->endOfWeek(Carbon::SUNDAY);

        // Indexar planificaciones por día para acceso O(1) en el grid
        $plansPorDia = [];
        foreach ($planificaciones as $plan) {
            $cursor = $plan->fecha_inicio->copy()->max($inicioMes);
            $limite = $plan->fecha_fin->copy()->min($finMes);
            while ($cursor->lte($limite)) {
                $key = $cursor->toDateString();
                $plansPorDia[$key][] = $plan;
                $cursor->addDay();
            }
        }

        $semanas = [];
        $cursor = $inicioGrid->copy();

        while ($cursor->lte($finGrid)) {
            $dias = [];
            for ($i = 0; $i < 7; $i++) {
                $fecha = $cursor->toDateString();
                $cursos = [];
                foreach ($plansPorDia[$fecha] ?? [] as $plan) {
                    $idx = $plan->curso_id % count(self::PALETTE);
                    $cursos[] = [
                        'id' => $plan->id,
                        'titulo' => $plan->curso->titulo ?? '—',
                        'bg' => self::PALETTE[$idx],
                    ];
                }

                $dias[] = [
                    'fecha' => $fecha,
                    'num' => $cursor->day,
                    'esMesActual' => $cursor->month === $this->mesActual && $cursor->year === $this->anioActual,
                    'esHoy' => $cursor->isSameDay($hoy),
                    'esPasado' => $cursor->lt($hoy) && ! $cursor->isSameDay($hoy),
                    'cursos' => $cursos,
                ];

                $cursor->addDay();
            }

            $semanas[] = ['dias' => $dias];
        }

        $this->semanasDelMes = $semanas;
    }

    private function construirListaMes($planificaciones, Carbon $inicioMes, Carbon $finMes): void
    {
        $hoy = Carbon::today();
        $result = [];

        foreach ($planificaciones as $plan) {
            if ($plan->curso === null) {
                continue;
            }

            $idx = $plan->curso_id % count(self::PALETTE);

            // Calcular estado dentro del mes
            $inicioPlan = $plan->fecha_inicio->copy();
            $finPlan = $plan->fecha_fin->copy();
            $activo = $inicioPlan->lte($hoy) && $finPlan->gte($hoy);

            $result[] = [
                'id' => $plan->id,
                'titulo' => $plan->curso->titulo,
                'fecha_inicio' => $plan->fecha_inicio->toDateString(),
                'fecha_fin' => $plan->fecha_fin->toDateString(),
                'sede_nombre' => $plan->sede->nombre ?? null,
                'bg' => self::PALETTE[$idx],
                'border' => self::PALETTE_BORDER[$idx],
                'activo' => $activo,
                'inicio_texto' => $this->formatFechaLarga($plan->fecha_inicio),
                'fin_texto' => $this->formatFechaLarga($plan->fecha_fin),
            ];
        }

        $this->cursosDelMes = $result;
    }

    private function construirProximos($user, Carbon $desdeDate): void
    {
        $desde = $desdeDate->copy()->addDay()->startOfDay();
        $hasta = $desde->copy()->addDays(60)->endOfDay();

        $planificaciones = PlanificacionCurso::with('curso:id,titulo', 'sede:id,nombre')
            ->where('fecha_inicio', '>=', $desde)
            ->where('fecha_inicio', '<=', $hasta)
            ->tap(fn (Builder $query) => $this->applyVisibility($query, $user))
            ->orderBy('fecha_inicio')
            ->get();

        $hoy = Carbon::today();
        $result = [];

        foreach ($planificaciones as $plan) {
            if ($plan->curso === null) {
                continue;
            }

            $idx = $plan->curso_id % count(self::PALETTE);
            $diasRest = $hoy->diffInDays($plan->fecha_inicio, false);

            $result[] = [
                'id' => $plan->id,
                'titulo' => $plan->curso->titulo,
                'fecha_inicio' => $plan->fecha_inicio->toDateString(),
                'sede_nombre' => $plan->sede->nombre ?? null,
                'bg' => self::PALETTE[$idx],
                'border' => self::PALETTE_BORDER[$idx],
                'inicio_texto' => $this->formatFechaLarga($plan->fecha_inicio),
                'dias_restantes' => (int) $diasRest,
            ];
        }

        $this->proximosCursos = $result;
    }

    private function applyVisibility(Builder $query, User $user): void
    {
        $isPreview = session('preview_mode', false);

        if ($isPreview && $user->hasAdminAccess()) {
            return;
        }

        if ($isPreview) {
            $query->whereHas('curso', function (Builder $query) use ($user): void {
                $query->where('capacitador_id', $user->id)
                    ->when($user->estamento_id, function (Builder $query) use ($user): void {
                        $query->orWhereHas('estamentos', function (Builder $query) use ($user): void {
                            $query->where('estamentos.id', $user->estamento_id);
                        });
                    });
            });

            return;
        }

        $query
            ->where(fn (Builder $query) => $query->whereNull('sede_id')->orWhere('sede_id', $user->sede_id))
            ->whereHas('curso.estamentos', function (Builder $query) use ($user): void {
                $query->where('estamentos.id', $user->estamento_id);
            });
    }

    private function formatFechaLarga(Carbon $fecha): string
    {
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        $dia = $dias[$fecha->dayOfWeekIso - 1];
        $mes = $meses[$fecha->month];

        return "{$dia} {$fecha->day} de {$mes} de {$fecha->year}";
    }

    public function render()
    {
        return view('livewire.calendario-usuario')
            ->extends('layouts.user')
            ->section('content');
    }
}
