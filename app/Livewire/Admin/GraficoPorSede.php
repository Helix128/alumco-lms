<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GraficoPorSede extends Component
{
    public array $chartData = [];

    public function mount(): void
    {
        $this->chartData = Cache::flexible(
            'admin_grafico_por_sede',
            [60, 300],
            fn (): array => $this->buildChartData(),
        );
    }

    public function render()
    {
        return view('livewire.admin.grafico-por-sede');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="h-[300px] w-full animate-pulse rounded-2xl bg-gray-100"></div>
        HTML;
    }

    private function buildChartData(): array
    {
        $currentYear = now()->year;

        $data = DB::table('users')
            ->join('sedes', 'sedes.id', '=', 'users.sede_id')
            ->leftJoin('certificados', function ($join) use ($currentYear) {
                $join->on('certificados.user_id', '=', 'users.id')
                    ->whereYear('certificados.fecha_emision', $currentYear);
            })
            ->where('users.activo', true)
            ->select(
                'sedes.nombre as sede',
                DB::raw('COUNT(DISTINCT users.id) as total'),
                DB::raw('COUNT(DISTINCT certificados.user_id) as con_certificado'),
            )
            ->groupBy('sedes.id', 'sedes.nombre')
            ->orderBy('sedes.nombre')
            ->get();

        $labels = [];
        $aprobados = [];
        $totales = [];

        foreach ($data as $row) {
            $labels[] = $row->sede;
            $porcentaje = $row->total > 0 ? round(($row->con_certificado / $row->total) * 100) : 0;
            $aprobados[] = $porcentaje;
            $totales[] = $row->total;
        }

        return [
            'labels' => $labels,
            'aprobados' => $aprobados,
            'totales' => $totales,
        ];
    }
}
