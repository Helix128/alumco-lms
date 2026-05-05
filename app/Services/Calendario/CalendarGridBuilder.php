<?php

namespace App\Services\Calendario;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarGridBuilder
{
    /**
     * @param  array<int, array{id: int|null, nombre: string}>  $sedes
     * @param  array<int, string>  $palette
     * @param  Collection<int, mixed>  $planificaciones
     * @return array{
     *     semanas: array<int, array{numero: int, inicio: string, fin: string, mes: int, esHoy: bool, esPasada: bool}>,
     *     meses: array<int, array{nombre: string, span: int, semanaInicio: int, semanaFin: int}>,
     *     filas: array<int, array{sede_id: int|null, nombre: string, semanas: array<int, array{cursos: array<int, array<string, mixed>>, conflicto: bool, cursos_popover?: array<int, array<string, mixed>>}>}>
     * }
     */
    public function buildAnnualData(int $anio, array $sedes, Collection $planificaciones, array $palette): array
    {
        $semanasBruto = $this->generateWeeks($anio);
        $hoy = Carbon::today();

        $semanasBase = [];
        foreach ($semanasBruto as $sem) {
            $fechaIni = Carbon::parse($sem['inicio']);
            $fechaFin = Carbon::parse($sem['fin']);
            $semanasBase[] = [
                'numero' => $sem['numero'],
                'inicio' => $sem['inicio'],
                'fin' => $sem['fin'],
                'mes' => $sem['mes'],
                'esHoy' => $fechaIni->lte($hoy) && $fechaFin->gte($hoy),
                'esPasada' => $fechaFin->lt($hoy),
            ];
        }

        $mesesDelAnio = $this->calculateMonths($semanasBase);

        $plansPorSede = [];
        foreach ($planificaciones as $plan) {
            $key = $plan->sede_id === null ? 'null' : (string) $plan->sede_id;
            $plansPorSede[$key][] = $plan;
        }

        $listaSedes = array_merge(
            [['id' => null, 'nombre' => 'Todas las sedes']],
            array_map(fn ($s) => ['id' => $s['id'], 'nombre' => $s['nombre']], $sedes)
        );

        $filas = [];
        foreach ($listaSedes as $sedeInfo) {
            $sedeKey = $sedeInfo['id'] === null ? 'null' : (string) $sedeInfo['id'];
            $plans = $plansPorSede[$sedeKey] ?? [];

            $cursosPorSemana = [];
            foreach ($plans as $plan) {
                $fechaInicio = $plan->fecha_inicio instanceof Carbon ? $plan->fecha_inicio : Carbon::parse($plan->fecha_inicio);
                $fechaFin = $plan->fecha_fin instanceof Carbon ? $plan->fecha_fin : Carbon::parse($plan->fecha_fin);

                $sIni = $this->weekForDate($fechaInicio, $semanasBruto);
                $sFin = $this->weekForDate($fechaFin, $semanasBruto);
                if ($sIni === null || $sFin === null) {
                    continue;
                }

                $colorIdx = $plan->curso_id % count($palette);
                for ($s = $sIni; $s <= $sFin; $s++) {
                    $cursosPorSemana[$s][] = [
                        'id' => $plan->id,
                        'curso_id' => $plan->curso_id,
                        'titulo' => $plan->curso->titulo ?? '—',
                        'bg' => $palette[$colorIdx],
                        'esInicio' => $s === $sIni,
                        'esFin' => $s === $sFin,
                        'notas' => $plan->notas,
                        'sede_id' => $plan->sede_id,
                        'sede_nombre' => $plan->sede->nombre ?? null,
                        'semaInicio' => $sIni,
                        'semaFin' => $sFin,
                    ];
                }
            }

            $semanasFila = [];
            foreach ($semanasBase as $sem) {
                $cursos = $cursosPorSemana[$sem['numero']] ?? [];
                $semanasFila[$sem['numero']] = [
                    'cursos' => $cursos,
                    'conflicto' => $this->hasSedeConflict($cursos),
                ];
            }

            $filas[] = [
                'sede_id' => $sedeInfo['id'],
                'nombre' => $sedeInfo['nombre'],
                'semanas' => $semanasFila,
            ];
        }

        if (count($filas) > 1) {
            foreach ($semanasBase as $sem) {
                $semNum = $sem['numero'];
                $cursosGlobales = $filas[0]['semanas'][$semNum]['cursos'];
                $globalConCursos = count($cursosGlobales) > 0;

                if (! $globalConCursos) {
                    continue;
                }

                $todosGlobalesConflicto = $cursosGlobales;
                $huboConflictoGlobal = false;

                for ($i = 1; $i < count($filas); $i++) {
                    $cursosEspecificos = $filas[$i]['semanas'][$semNum]['cursos'];
                    if (count($cursosEspecificos) > 0) {
                        $filas[0]['semanas'][$semNum]['conflicto'] = true;
                        $filas[$i]['semanas'][$semNum]['conflicto'] = true;
                        $huboConflictoGlobal = true;

                        $todos = array_merge($cursosGlobales, $cursosEspecificos);
                        $filas[$i]['semanas'][$semNum]['cursos_popover'] = $todos;

                        $todosGlobalesConflicto = array_merge($todosGlobalesConflicto, $cursosEspecificos);
                    }
                }

                if ($huboConflictoGlobal) {
                    $filas[0]['semanas'][$semNum]['cursos_popover'] = $todosGlobalesConflicto;
                }
            }
        }

        return [
            'semanas' => $semanasBase,
            'meses' => $mesesDelAnio,
            'filas' => $filas,
        ];
    }

    /**
     * @return array<int, array{numero: int, inicio: string, fin: string, mes: int}>
     */
    public function generateWeeks(int $anio): array
    {
        $cursor = Carbon::create($anio, 1, 1)->startOfWeek(Carbon::MONDAY);
        $semanas = [];
        $numero = 1;

        while (true) {
            $fin = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

            if ($fin->year < $anio) {
                $cursor->addWeek();

                continue;
            }

            if ($cursor->year > $anio) {
                break;
            }

            $semanas[] = [
                'numero' => $numero++,
                'inicio' => $cursor->toDateString(),
                'fin' => $fin->toDateString(),
                'mes' => $this->monthOfWeek($cursor->copy(), $fin->copy()),
            ];

            $cursor->addWeek();
        }

        return $semanas;
    }

    /**
     * @param  array<int, array{numero: int, inicio: string, fin: string}>  $semanas
     */
    public function weekForDate(Carbon $fecha, array $semanas): ?int
    {
        foreach ($semanas as $sem) {
            if ($fecha->between(
                Carbon::parse($sem['inicio'])->startOfDay(),
                Carbon::parse($sem['fin'])->endOfDay()
            )) {
                return $sem['numero'];
            }
        }

        if (! empty($semanas)) {
            $primera = Carbon::parse($semanas[0]['inicio']);
            $ultima = Carbon::parse($semanas[count($semanas) - 1]['fin']);
            if ($fecha->lt($primera)) {
                return $semanas[0]['numero'];
            }
            if ($fecha->gt($ultima)) {
                return $semanas[count($semanas) - 1]['numero'];
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{numero: int, mes: int}>  $semanas
     * @return array<int, array{nombre: string, span: int, semanaInicio: int, semanaFin: int}>
     */
    public function calculateMonths(array $semanas): array
    {
        $nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $meses = [];
        $ultimo = null;

        foreach ($semanas as $sem) {
            $mes = $sem['mes'];
            if ($mes !== $ultimo) {
                $meses[] = [
                    'nombre' => $nombresMeses[$mes - 1],
                    'span' => 1,
                    'semanaInicio' => $sem['numero'],
                    'semanaFin' => $sem['numero'],
                ];
                $ultimo = $mes;
            } else {
                $meses[count($meses) - 1]['span']++;
                $meses[count($meses) - 1]['semanaFin'] = $sem['numero'];
            }
        }

        return $meses;
    }

    private function monthOfWeek(Carbon $lunes, Carbon $domingo): int
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
     * @param  array<int, array<string, mixed>>  $cursos
     */
    private function hasSedeConflict(array $cursos): bool
    {
        $n = count($cursos);
        if ($n < 2) {
            return false;
        }

        $unicos = [];
        foreach ($cursos as $c) {
            $unicos[$c['id']] = $c;
        }
        $unicos = array_values($unicos);

        for ($i = 0; $i < count($unicos); $i++) {
            for ($j = $i + 1; $j < count($unicos); $j++) {
                $sedeA = $unicos[$i]['sede_id'] ?? null;
                $sedeB = $unicos[$j]['sede_id'] ?? null;
                if ($sedeA === null || $sedeB === null || $sedeA === $sedeB) {
                    return true;
                }
            }
        }

        return false;
    }
}
