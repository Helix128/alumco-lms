<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GraficoComposicion extends Component
{
    public array $porEstamento = [];

    public array $porAprobacion = [];

    public array $porSexo = [];

    public function mount(): void
    {
        $this->buildChartData();
    }

    public function render()
    {
        return view('livewire.admin.grafico-composicion');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="h-[300px] animate-pulse rounded-2xl bg-gray-100"></div>
            <div class="h-[300px] animate-pulse rounded-2xl bg-gray-100"></div>
            <div class="h-[300px] animate-pulse rounded-2xl bg-gray-100"></div>
        </div>
        HTML;
    }

    private function buildChartData(): void
    {
        $this->porEstamento = Cache::flexible(
            'admin_chart_estamento',
            [60, 300],
            fn (): array => $this->getEstamentoData(),
        );

        $this->porAprobacion = Cache::flexible(
            'admin_chart_aprobacion',
            [60, 300],
            fn (): array => $this->getAprobacionData(),
        );

        $this->porSexo = Cache::flexible(
            'admin_chart_sexo',
            [60, 300],
            fn (): array => $this->getSexoData(),
        );
    }

    private function getEstamentoData(): array
    {
        $data = DB::table('users')
            ->join('estamentos', 'estamentos.id', '=', 'users.estamento_id')
            ->where('users.activo', true)
            ->select('estamentos.nombre', DB::raw('COUNT(users.id) as total'))
            ->groupBy('estamentos.id', 'estamentos.nombre')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'labels' => $data->pluck('nombre')->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }

    private function getAprobacionData(): array
    {
        $currentYear = now()->year;

        $conCertificado = DB::table('users')
            ->join('certificados', 'certificados.user_id', '=', 'users.id')
            ->where('users.activo', true)
            ->whereYear('certificados.fecha_emision', $currentYear)
            ->distinct('users.id')
            ->count('users.id');

        $sinCertificado = User::where('activo', true)->count() - $conCertificado;

        return [
            'labels' => ['Con Certificado', 'Sin Certificado'],
            'data' => [$conCertificado, $sinCertificado],
        ];
    }

    private function getSexoData(): array
    {
        $data = DB::table('users')
            ->where('activo', true)
            ->select('sexo', DB::raw('COUNT(*) as total'))
            ->groupBy('sexo')
            ->orderBy('total', 'desc')
            ->get();

        $labelMap = [
            'F' => 'Femenino',
            'M' => 'Masculino',
            'Otro' => 'Otro',
        ];

        return [
            'labels' => $data->map(fn ($row) => $labelMap[$row->sexo] ?? $row->sexo)->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }
}
