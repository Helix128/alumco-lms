<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class DistribucionEtaria extends Component
{
    public array $chartData = [];

    public int $totalUsuarios = 0;

    public string $rangoDominante = 'Sin datos';

    public int $rangoDominanteCantidad = 0;

    public function mount(): void
    {
        $this->buildChartData();
    }

    public function render(): View
    {
        return view('livewire.admin.distribucion-etaria');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="chart-panel p-6">
            <div class="chart-panel__placeholder chart-panel__placeholder--lg">Cargando distribución etaria...</div>
        </div>
        HTML;
    }

    private function buildChartData(): void
    {
        $data = Cache::flexible(
            'admin_chart_distribucion_etaria',
            [60, 300],
            fn (): array => $this->queryChartData(),
        );

        $this->chartData = $data;
        $this->totalUsuarios = array_sum($data['data'] ?? []);
        $this->rangoDominante = (string) ($data['leader']['label'] ?? 'Sin datos');
        $this->rangoDominanteCantidad = (int) ($data['leader']['total'] ?? 0);
    }

    private function queryChartData(): array
    {
        $rows = DB::table('users')
            ->where('activo', true)
            ->whereNotNull('fecha_nacimiento')
            ->selectRaw(
                "CASE
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
                    ELSE '56+'
                END as rango,
                COUNT(*) as total",
            )
            ->groupBy('rango')
            ->orderByRaw("FIELD(rango, '18-25', '26-35', '36-45', '46-55', '56+')")
            ->get();

        $labels = ['18-25', '26-35', '36-45', '46-55', '56+'];
        $data = array_fill(0, count($labels), 0);

        foreach ($rows as $row) {
            $index = array_search($row->rango, $labels, true);
            if ($index !== false) {
                $data[$index] = (int) $row->total;
            }
        }

        $leaderIndex = array_search(max($data ?: [0]), $data, true);

        return [
            'labels' => $labels,
            'data' => $data,
            'leader' => $leaderIndex === false ? ['label' => null, 'total' => 0] : [
                'label' => $labels[$leaderIndex],
                'total' => $data[$leaderIndex],
            ],
        ];
    }
}
