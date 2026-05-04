<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Defer;
use Livewire\Component;

#[Defer]
class EstadisticasDashboard extends Component
{
    public int $capacitadorId;

    /** @var array Chart data: [{label, value, color}] */
    public array $chartData = [];

    public function mount(int $capacitadorId): void
    {
        $this->capacitadorId = $capacitadorId;

        $this->chartData = Cache::flexible(
            "capacitador_dashboard_chart_{$capacitadorId}",
            [30, 120],
            fn (): array => $this->buildChartData($capacitadorId),
        );
    }

    public function render()
    {
        return view('livewire.capacitador.estadisticas-dashboard');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="space-y-3 py-2">
            <div class="h-5 w-1/3 animate-pulse rounded bg-Alumco-blue/10"></div>
            <div class="h-52 w-full animate-pulse rounded-2xl bg-gray-100"></div>
        </div>
        HTML;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildChartData(int $capacitadorId): array
    {
        $cursos = Curso::query()
            ->where('capacitador_id', $capacitadorId)
            ->withCount('modulos')
            ->get(['id', 'titulo']);

        if ($cursos->isEmpty()) {
            return [];
        }

        $courseIds = $cursos->pluck('id')->all();

        $progresoPorUsuario = DB::table('progreso_modulos as progreso')
            ->join('modulos as modulo', 'modulo.id', '=', 'progreso.modulo_id')
            ->whereIn('modulo.curso_id', $courseIds)
            ->selectRaw('modulo.curso_id as curso_id')
            ->selectRaw('progreso.user_id as user_id')
            ->selectRaw('COUNT(DISTINCT progreso.modulo_id) as modulos_con_avance')
            ->selectRaw('COUNT(DISTINCT CASE WHEN progreso.completado = 1 THEN progreso.modulo_id END) as modulos_completados')
            ->groupBy('modulo.curso_id', 'progreso.user_id')
            ->get()
            ->groupBy('curso_id');

        return $cursos->map(function (Curso $curso) use ($progresoPorUsuario): array {
            $totalModulos = (int) $curso->modulos_count;

            if ($totalModulos === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            $resumenUsuarios = $progresoPorUsuario->get($curso->id, collect());
            $usuariosTotal = $resumenUsuarios->count();

            if ($usuariosTotal === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            $completaron = $resumenUsuarios->filter(
                fn ($item): bool => (int) $item->modulos_completados >= $totalModulos
            )->count();

            return [
                'label' => $curso->titulo,
                'value' => (int) round(($completaron / $usuariosTotal) * 100),
            ];
        })->values()->all();
    }
}
