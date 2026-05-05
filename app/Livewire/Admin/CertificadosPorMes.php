<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class CertificadosPorMes extends Component
{
    public array $chartData = [];

    public int $currentYear;

    public int $totalCertificados = 0;

    public string $mesPico = 'Sin datos';

    public int $certificadosMesPico = 0;

    public function mount(): void
    {
        $this->currentYear = now()->year;
        $this->buildChartData();
    }

    public function render(): View
    {
        return view('livewire.admin.certificados-por-mes');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="chart-panel p-6">
            <div class="chart-panel__placeholder chart-panel__placeholder--lg">Cargando evolución de certificados...</div>
        </div>
        HTML;
    }

    private function buildChartData(): void
    {
        $data = Cache::flexible(
            "admin_chart_certificados_mensuales_{$this->currentYear}",
            [60, 300],
            fn (): array => $this->queryChartData(),
        );

        $this->chartData = $data;
        $this->totalCertificados = array_sum($data['data'] ?? []);
        $this->certificadosMesPico = (int) ($data['peak']['total'] ?? 0);
        $this->mesPico = (string) ($data['peak']['label'] ?? 'Sin datos');
    }

    private function queryChartData(): array
    {
        $labels = [
            'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic',
        ];

        $data = DB::table('certificados')
            ->whereYear('fecha_emision', $this->currentYear)
            ->selectRaw('MONTH(fecha_emision) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $series = collect(range(1, 12))
            ->map(fn (int $month): int => (int) ($data[$month] ?? 0))
            ->all();

        $peakIndex = array_search(max($series), $series, true);
        $peakMonth = $peakIndex === false ? null : $peakIndex + 1;

        return [
            'labels' => $labels,
            'data' => $series,
            'peak' => [
                'label' => $peakMonth ? $labels[$peakMonth - 1] : null,
                'total' => $peakMonth ? $series[$peakMonth - 1] : 0,
            ],
        ];
    }
}
