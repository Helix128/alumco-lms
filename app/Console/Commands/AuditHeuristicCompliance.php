<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\SeccionCurso;
use App\Support\HelpCenter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AuditHeuristicCompliance extends Command
{
    protected $signature = 'lms:audit-heuristics';

    protected $description = 'Audita controles reproducibles de heurísticas, accesibilidad y enlaces internos';

    public function handle(HelpCenter $help): int
    {
        $failures = [];
        $checks = [];
        $check = function (bool $passes, string $label) use (&$checks, &$failures): void {
            $checks[] = [$passes, $label];
            if (! $passes) {
                $failures[] = $label;
            }
        };

        $check(count($help->topics()) >= 13, 'Centro de ayuda con temas por tarea y rol');
        $check(Route::has('help.index') && Route::has('help.show'), 'Rutas públicas del centro de ayuda');
        $check(Route::has('history.undo') && Route::has('history.redo'), 'Rutas autenticadas de Deshacer/Rehacer');

        foreach ([Curso::class, SeccionCurso::class, Modulo::class, PlanificacionCurso::class, Evaluacion::class, Pregunta::class, Opcion::class] as $model) {
            $check(in_array(SoftDeletes::class, class_uses_recursive($model), true), class_basename($model).' usa eliminación lógica');
        }

        $viewContents = collect(File::allFiles(resource_path('views')))
            ->map(fn ($file): string => File::get($file->getPathname()))
            ->implode("\n");
        $check(! preg_match('/\b(?:window\.)?confirm\s*\(/', $viewContents), 'Sin confirmaciones nativas aisladas');
        $check(! preg_match('/wire:loading[^>]*>\s*\.\.\.\s*</s', $viewContents), 'Estados Livewire con texto significativo');
        $check(str_contains(File::get(resource_path('css/app.css')), '[data-motion="reduced"] *'), 'Reducción global de movimiento');
        $check(str_contains(File::get(resource_path('css/app.css')), 'min-height: 2.75rem'), 'Objetivos táctiles mínimos de 44 px');

        preg_match_all('/\broute\(\s*[\'\"]([^\'\"]+)[\'\"]/', $viewContents, $routeMatches);
        $missingRoutes = collect($routeMatches[1])->unique()->reject(fn (string $name): bool => Route::has($name));
        $check($missingRoutes->isEmpty(), 'Enlaces Blade apuntan a rutas registradas'.($missingRoutes->isEmpty() ? '' : ': '.$missingRoutes->join(', ')));

        $documentation = File::exists(base_path('docs/cumplimiento-analisis-heuristico.md'))
            ? File::get(base_path('docs/cumplimiento-analisis-heuristico.md'))
            : '';
        preg_match_all('/^\|\s*\d+\s*\|/m', $documentation, $criterionRows);
        $check(count($criterionRows[0]) === 52, 'Matriz documentada con los 52 criterios');
        $check(substr_count($documentation, '0 — Sin problemas') >= 52, 'Los 52 criterios cierran con severidad 0');

        foreach ($checks as [$passes, $label]) {
            $passes ? $this->components->info($label) : $this->components->error($label);
        }

        $this->newLine();
        $this->line(sprintf('%d controles aprobados; %d observaciones.', count($checks) - count($failures), count($failures)));

        return $failures === [] ? self::SUCCESS : self::FAILURE;
    }
}
