<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\ProgresoModulo;
use Livewire\Component;

class EstadisticasDashboard extends Component
{
    public int $capacitadorId;

    /** @var array Chart data: [{label, value, color}] */
    public array $chartData = [];

    public function mount(int $capacitadorId): void
    {
        $this->capacitadorId = $capacitadorId;

        $cursos = Curso::where('capacitador_id', $capacitadorId)
            ->withCount('modulos')
            ->get();

        $this->chartData = $cursos->map(function (Curso $curso) {
            $totalModulos = $curso->modulos_count;

            if ($totalModulos === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            // Usuarios únicos con al menos un progreso en este curso
            $moduloIds = $curso->modulos()->pluck('id');
            $usuariosTotal = ProgresoModulo::whereIn('modulo_id', $moduloIds)
                ->distinct('user_id')
                ->count('user_id');

            if ($usuariosTotal === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            // Usuarios que completaron TODOS los módulos
            $completaron = ProgresoModulo::whereIn('modulo_id', $moduloIds)
                ->where('completado', true)
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= ?', [$totalModulos])
                ->count();

            $porcentaje = (int) round(($completaron / $usuariosTotal) * 100);
            return ['label' => $curso->titulo, 'value' => $porcentaje];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.capacitador.estadisticas-dashboard');
    }
}
