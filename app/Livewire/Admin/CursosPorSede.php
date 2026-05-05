<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class CursosPorSede extends Component
{
    public array $chartData = [];

    public int $currentYear;

    public int $totalCursos = 0;

    public int $totalPlanificaciones = 0;

    public string $sedeLider = 'Sin datos';

    public int $sedeLiderCantidad = 0;

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->buildChartData();
    }

    public function render(): View
    {
        return view('livewire.admin.cursos-por-sede');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="chart-panel p-6">
            <div class="chart-panel__placeholder chart-panel__placeholder--lg">Cargando distribución de cursos por sede...</div>
        </div>
        HTML;
    }

    private function buildChartData(): void
    {
        $data = Cache::flexible(
            "admin_chart_cursos_por_sede_{$this->currentYear}",
            [60, 300],
            fn (): array => $this->queryChartData(),
        );

        $this->chartData = $data;
        $this->totalCursos = array_sum($data['uniqueCourses'] ?? []);
        $this->totalPlanificaciones = array_sum($data['plannedSessions'] ?? []);
        $this->sedeLider = (string) ($data['leader']['label'] ?? 'Sin datos');
        $this->sedeLiderCantidad = (int) ($data['leader']['total'] ?? 0);
    }

    private function queryChartData(): array
    {
        $rows = DB::table('planificaciones_cursos')
            ->join('sedes', 'sedes.id', '=', 'planificaciones_cursos.sede_id')
            ->whereYear('planificaciones_cursos.fecha_inicio', $this->currentYear)
            ->select(
                'sedes.nombre as sede',
                DB::raw('COUNT(DISTINCT planificaciones_cursos.curso_id) as cursos_unicos'),
                DB::raw('COUNT(*) as planificaciones'),
            )
            ->groupBy('sedes.id', 'sedes.nombre')
            ->orderByDesc('cursos_unicos')
            ->orderBy('sedes.nombre')
            ->get();

        $labels = [];
        $uniqueCourses = [];
        $plannedSessions = [];

        foreach ($rows as $row) {
            $labels[] = $row->sede;
            $uniqueCourses[] = (int) $row->cursos_unicos;
            $plannedSessions[] = (int) $row->planificaciones;
        }

        $leaderIndex = array_search(max($uniqueCourses ?: [0]), $uniqueCourses, true);

        return [
            'labels' => $labels,
            'uniqueCourses' => $uniqueCourses,
            'plannedSessions' => $plannedSessions,
            'leader' => $leaderIndex === false ? ['label' => null, 'total' => 0] : [
                'label' => $labels[$leaderIndex],
                'total' => $uniqueCourses[$leaderIndex],
            ],
        ];
    }
}
