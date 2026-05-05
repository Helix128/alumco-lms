<?php

namespace App\Actions\Calendario;

use App\Models\PlanificacionCurso;
use App\Services\Calendario\CalendarGridBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CopyYearPlanningAction
{
    public function __construct(
        private readonly CalendarGridBuilder $calendarGridBuilder
    ) {}

    /**
     * @return array{status: 'copied'|'requires_confirmation', copied: int}
     */
    public function execute(int $anioOrigen, int $anioDestino, string $modo = 'auto'): array
    {
        if (! in_array($modo, ['auto', 'append', 'replace'], true)) {
            throw new \InvalidArgumentException('Selecciona una acción válida para copiar la planificación.');
        }

        if ($anioDestino === $anioOrigen) {
            throw new \InvalidArgumentException('El año destino debe ser diferente al actual.');
        }

        $inicioDestino = Carbon::create($anioDestino, 1, 1)->startOfDay();
        $finDestino = Carbon::create($anioDestino, 12, 31)->endOfDay();

        $existentes = PlanificacionCurso::where('fecha_inicio', '<=', $finDestino)
            ->where('fecha_fin', '>=', $inicioDestino)
            ->exists();

        if ($existentes && $modo === 'auto') {
            return [
                'status' => 'requires_confirmation',
                'copied' => 0,
            ];
        }

        $semanasOrigen = $this->calendarGridBuilder->generateWeeks($anioOrigen);
        $semanasDestino = $this->calendarGridBuilder->generateWeeks($anioDestino);
        $totalDestino = count($semanasDestino);

        $limiteInfOrigen = Carbon::parse($semanasOrigen[0]['inicio'])->startOfDay();
        $limiteSuperOrigen = Carbon::parse($semanasOrigen[count($semanasOrigen) - 1]['fin'])->endOfDay();

        $planificaciones = PlanificacionCurso::where('fecha_inicio', '<=', $limiteSuperOrigen)
            ->where('fecha_fin', '>=', $limiteInfOrigen)
            ->get();

        if ($planificaciones->isEmpty()) {
            throw new \RuntimeException("No hay planificaciones en {$anioOrigen} para copiar.");
        }

        $copied = 0;
        DB::transaction(function () use ($existentes, $finDestino, $inicioDestino, $modo, $planificaciones, $semanasDestino, $semanasOrigen, $totalDestino, &$copied): void {
            if ($existentes && $modo === 'replace') {
                PlanificacionCurso::where('fecha_inicio', '<=', $finDestino)
                    ->where('fecha_fin', '>=', $inicioDestino)
                    ->delete();
            }

            foreach ($planificaciones as $plan) {
                $semIni = $this->calendarGridBuilder->weekForDate($plan->fecha_inicio, $semanasOrigen);
                $semFin = $this->calendarGridBuilder->weekForDate($plan->fecha_fin, $semanasOrigen);

                if ($semIni === null || $semFin === null) {
                    continue;
                }

                $semIni = max(1, min($semIni, $totalDestino));
                $semFin = max(1, min($semFin, $totalDestino));

                PlanificacionCurso::create([
                    'curso_id' => $plan->curso_id,
                    'sede_id' => $plan->sede_id,
                    'fecha_inicio' => $semanasDestino[$semIni - 1]['inicio'],
                    'fecha_fin' => $semanasDestino[$semFin - 1]['fin'],
                    'notas' => $plan->notas,
                ]);
                $copied++;
            }
        });

        return [
            'status' => 'copied',
            'copied' => $copied,
        ];
    }
}
