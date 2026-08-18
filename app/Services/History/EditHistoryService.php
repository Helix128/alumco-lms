<?php

namespace App\Services\History;

use App\Exceptions\EditHistoryConflict;
use App\Models\Curso;
use App\Models\EditHistory;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\ReportePreset;
use App\Models\SeccionCurso;
use App\Models\User;
use App\Services\Cursos\CoursePlanningNotifier;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class EditHistoryService
{
    public const CourseStructure = 'course_structure';

    public const Evaluation = 'evaluation';

    public const Calendar = 'calendar';

    public const Reports = 'reports';

    public const GlobalScope = 'global';

    public const MaxSteps = 20;

    public const LifetimeMinutes = 30;

    /** @return list<string> */
    public static function contexts(): array
    {
        return [self::CourseStructure, self::Evaluation, self::Calendar, self::Reports];
    }

    public function captureChange(
        User $user,
        string $context,
        string|int $scopeId,
        string $label,
        Closure $change,
    ): mixed {
        return DB::transaction(function () use ($user, $context, $scopeId, $label, $change): mixed {
            $before = $this->snapshot($user, $context, $scopeId);
            $result = $change();
            $after = $this->snapshot($user, $context, $scopeId);
            $this->recordStates($user, $context, $scopeId, $label, $before, $after);

            return $result;
        });
    }

    /** @param array<string, mixed> $before @param array<string, mixed> $after */
    public function recordStates(
        User $user,
        string $context,
        string|int $scopeId,
        string $label,
        array $before,
        array $after,
    ): ?EditHistory {
        $this->authorize($user, $context, (string) $scopeId);
        $beforeHash = $this->hash($before);
        $afterHash = $this->hash($after);

        if (hash_equals($beforeHash, $afterHash)) {
            return null;
        }

        $query = EditHistory::query()->where([
            'user_id' => $user->id,
            'context' => $context,
            'scope_id' => (string) $scopeId,
        ]);

        // Una acción nueva invalida la cadena de Rehacer.
        (clone $query)->whereNotNull('undone_at')->delete();

        $history = EditHistory::create([
            'user_id' => $user->id,
            'context' => $context,
            'scope_id' => (string) $scopeId,
            'label' => $label,
            'before_state' => $before,
            'after_state' => $after,
            'before_hash' => $beforeHash,
            'after_hash' => $afterHash,
            'expires_at' => now()->addMinutes(self::LifetimeMinutes),
        ]);

        $obsoleteIds = (clone $query)
            ->orderByDesc('id')
            ->skip(self::MaxSteps)
            ->take(PHP_INT_MAX)
            ->pluck('id');
        if ($obsoleteIds->isNotEmpty()) {
            EditHistory::whereKey($obsoleteIds)->delete();
        }

        return $history;
    }

    /** @return array<string, mixed> */
    public function snapshot(User $user, string $context, string|int $scopeId): array
    {
        $scopeId = (string) $scopeId;
        $this->authorize($user, $context, $scopeId);

        return match ($context) {
            self::CourseStructure => $this->courseStructureSnapshot((int) $scopeId),
            self::Evaluation => $this->evaluationSnapshot((int) $scopeId),
            self::Calendar => $this->calendarSnapshot(),
            self::Reports => $this->reportsSnapshot(),
            default => abort(404),
        };
    }

    public function undo(User $user, string $context, string|int $scopeId): EditHistory
    {
        return $this->travel($user, $context, (string) $scopeId, false);
    }

    public function redo(User $user, string $context, string|int $scopeId): EditHistory
    {
        return $this->travel($user, $context, (string) $scopeId, true);
    }

    /** @return array{can_undo: bool, can_redo: bool, undo_label: ?string, redo_label: ?string} */
    public function availability(User $user, string $context, string|int $scopeId): array
    {
        if (! Schema::hasTable('edit_histories')) {
            return ['can_undo' => false, 'can_redo' => false, 'undo_label' => null, 'redo_label' => null];
        }

        $this->authorize($user, $context, (string) $scopeId);
        $base = EditHistory::query()
            ->where('user_id', $user->id)
            ->where('context', $context)
            ->where('scope_id', (string) $scopeId)
            ->where('expires_at', '>', now());
        $undo = (clone $base)->whereNull('undone_at')->latest('id')->first();
        $redo = (clone $base)->whereNotNull('undone_at')->oldest('id')->first();

        return [
            'can_undo' => $undo !== null,
            'can_redo' => $redo !== null,
            'undo_label' => $undo?->label,
            'redo_label' => $redo?->label,
        ];
    }

    private function travel(User $user, string $context, string $scopeId, bool $redo): EditHistory
    {
        return DB::transaction(function () use ($user, $context, $scopeId, $redo): EditHistory {
            $this->authorize($user, $context, $scopeId);
            $base = EditHistory::query()
                ->where('user_id', $user->id)
                ->where('context', $context)
                ->where('scope_id', $scopeId)
                ->where('expires_at', '>', now())
                ->lockForUpdate();

            $history = $redo
                ? (clone $base)->whereNotNull('undone_at')->oldest('id')->first()
                : (clone $base)->whereNull('undone_at')->latest('id')->first();

            abort_if($history === null, 409, $redo ? 'No hay cambios para rehacer.' : 'No hay cambios para deshacer.');

            $current = $this->snapshot($user, $context, $scopeId);
            $expectedHash = $redo ? $history->before_hash : $history->after_hash;
            if (! hash_equals($expectedHash, $this->hash($current))) {
                throw EditHistoryConflict::contentChanged();
            }

            $target = $redo ? $history->after_state : $history->before_state;
            $this->restore($context, $scopeId, $target, $current);
            $history->forceFill(['undone_at' => $redo ? null : now()])->save();

            return $history;
        });
    }

    private function authorize(User $user, string $context, string $scopeId): void
    {
        abort_unless(in_array($context, self::contexts(), true), 404);

        if ($context === self::CourseStructure) {
            $course = Curso::withTrashed()->findOrFail((int) $scopeId);
            Gate::forUser($user)->authorize('manage', $course);

            return;
        }

        if ($context === self::Evaluation) {
            $evaluation = Evaluacion::withTrashed()->with(['modulo' => fn ($query) => $query->withTrashed()])->findOrFail((int) $scopeId);
            $module = $evaluation->modulo;
            abort_if($module === null, 404);
            $course = Curso::withTrashed()->findOrFail($module->curso_id);
            Gate::forUser($user)->authorize('manage', $course);

            return;
        }

        abort_unless($scopeId === self::GlobalScope && $user->hasAdminAccess(), 403);
    }

    /** @return array<string, mixed> */
    private function courseStructureSnapshot(int $courseId): array
    {
        $course = Curso::withTrashed()->findOrFail($courseId);

        return [
            'course' => $this->attributes($course, [
                'id', 'titulo', 'descripcion', 'nota_capacitador', 'imagen_portada', 'color_promedio', 'deleted_at',
            ]),
            'sections' => SeccionCurso::where('curso_id', $courseId)->orderBy('id')->get()
                ->map(fn (SeccionCurso $section): array => $this->attributes($section, ['id', 'curso_id', 'titulo', 'orden', 'deleted_at']))->all(),
            'modules' => Modulo::where('curso_id', $courseId)->orderBy('id')->get()
                ->map(fn (Modulo $module): array => $this->attributes($module, [
                    'id', 'curso_id', 'seccion_id', 'titulo', 'orden', 'tipo_contenido', 'ruta_archivo',
                    'nombre_archivo_original', 'contenido', 'duracion_minutos', 'deleted_at',
                ]))->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function evaluationSnapshot(int $evaluationId): array
    {
        $evaluation = Evaluacion::withTrashed()->findOrFail($evaluationId);
        $questions = Pregunta::where('evaluacion_id', $evaluationId)->orderBy('id')->get();
        $questionIds = $questions->pluck('id');

        return [
            'evaluation' => $this->attributes($evaluation, ['id', 'modulo_id', 'deleted_at']),
            'questions' => $questions->map(fn (Pregunta $question): array => $this->attributes($question, ['id', 'evaluacion_id', 'enunciado', 'orden', 'deleted_at']))->all(),
            'options' => Opcion::whereIn('pregunta_id', $questionIds)->orderBy('id')->get()
                ->map(fn (Opcion $option): array => $this->attributes($option, ['id', 'pregunta_id', 'texto', 'es_correcta', 'orden', 'deleted_at']))->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function calendarSnapshot(): array
    {
        return [
            'plans' => PlanificacionCurso::orderBy('id')->get()
                ->map(fn (PlanificacionCurso $plan): array => $this->attributes($plan, ['id', 'curso_id', 'sede_id', 'fecha_inicio', 'fecha_fin', 'notas', 'deleted_at']))->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function reportsSnapshot(): array
    {
        return [
            'presets' => ReportePreset::orderBy('id')->get()
                ->map(fn (ReportePreset $preset): array => $this->attributes($preset, ['id', 'nombre', 'columnas', 'deleted_at']))->all(),
        ];
    }

    /** @param array<string, mixed> $target @param array<string, mixed> $current */
    private function restore(string $context, string $scopeId, array $target, array $current): void
    {
        match ($context) {
            self::CourseStructure => $this->restoreCourseStructure((int) $scopeId, $target),
            self::Evaluation => $this->restoreEvaluation((int) $scopeId, $target),
            self::Calendar => $this->restoreCalendar($target, $current),
            self::Reports => $this->syncRows(ReportePreset::class, [], $target['presets'], ['nombre', 'columnas', 'deleted_at']),
            default => abort(404),
        };
    }

    /** @param array<string, mixed> $state */
    private function restoreCourseStructure(int $courseId, array $state): void
    {
        Curso::withTrashed()->whereKey($courseId)->update(Arr::only($state['course'], [
            'titulo', 'descripcion', 'nota_capacitador', 'imagen_portada', 'color_promedio', 'deleted_at',
        ]));
        $this->syncRows(SeccionCurso::class, ['curso_id' => $courseId], $state['sections'], ['titulo', 'orden', 'deleted_at']);
        $this->syncRows(Modulo::class, ['curso_id' => $courseId], $state['modules'], [
            'seccion_id', 'titulo', 'orden', 'tipo_contenido', 'ruta_archivo',
            'nombre_archivo_original', 'contenido', 'duracion_minutos', 'deleted_at',
        ]);
    }

    /** @param array<string, mixed> $state */
    private function restoreEvaluation(int $evaluationId, array $state): void
    {
        Evaluacion::withTrashed()->whereKey($evaluationId)->update(['deleted_at' => $state['evaluation']['deleted_at']]);
        $questionIds = Pregunta::withTrashed()->where('evaluacion_id', $evaluationId)->pluck('id');
        $this->syncRows(Pregunta::class, ['evaluacion_id' => $evaluationId], $state['questions'], ['enunciado', 'orden', 'deleted_at']);
        $this->syncRows(Opcion::class, fn ($query) => $query->whereIn('pregunta_id', $questionIds), $state['options'], ['pregunta_id', 'texto', 'es_correcta', 'orden', 'deleted_at']);
    }

    /** @param array<string, mixed> $target @param array<string, mixed> $current */
    private function restoreCalendar(array $target, array $current): void
    {
        $currentById = collect($current['plans'])->keyBy('id');
        $targetById = collect($target['plans'])->keyBy('id');
        $changedIds = $currentById->keys()->merge($targetById->keys())->unique()->filter(
            fn ($id): bool => $currentById->get($id) !== $targetById->get($id)
        );

        $this->syncRows(PlanificacionCurso::class, [], $target['plans'], ['curso_id', 'sede_id', 'fecha_inicio', 'fecha_fin', 'notas', 'deleted_at']);

        foreach ($changedIds as $id) {
            $plan = PlanificacionCurso::find($id);
            if ($plan) {
                app(CoursePlanningNotifier::class)->notifyUpdated($plan);
            }
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>|Closure  $scope
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $fields
     */
    private function syncRows(string $modelClass, array|Closure $scope, array $rows, array $fields): void
    {
        $query = $modelClass::withTrashed();
        $scope instanceof Closure ? $scope($query) : $query->where($scope);
        $existingIds = $query->pluck('id');
        $targetIds = collect($rows)->pluck('id');

        foreach ($rows as $row) {
            $model = $modelClass::withTrashed()->findOrFail($row['id']);
            $model->forceFill(Arr::only($row, $fields))->saveQuietly();
        }

        $removedIds = $existingIds->diff($targetIds);
        if ($removedIds->isNotEmpty()) {
            $modelClass::withTrashed()->whereKey($removedIds)->update(['deleted_at' => now()]);
        }
    }

    /** @param list<string> $keys @return array<string, mixed> */
    private function attributes(Model $model, array $keys): array
    {
        $attributes = Arr::only($model->getAttributes(), $keys);
        ksort($attributes);

        foreach ($attributes as $key => $value) {
            if ($model->hasCast($key)) {
                $attributes[$key] = $model->getAttribute($key);
                if ($attributes[$key] instanceof \DateTimeInterface) {
                    $attributes[$key] = $attributes[$key]->format('Y-m-d H:i:s');
                }
            }
        }

        return $attributes;
    }

    /** @param array<string, mixed> $state */
    private function hash(array $state): string
    {
        return hash('sha256', json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
