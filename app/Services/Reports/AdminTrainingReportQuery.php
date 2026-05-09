<?php

namespace App\Services\Reports;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use App\Support\Reports\ReportFilters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class AdminTrainingReportQuery
{
    /**
     * @return array{estamentos: EloquentCollection<int, Estamento>, cursos: EloquentCollection<int, Curso>, sedes: EloquentCollection<int, Sede>}
     */
    public function catalogs(): array
    {
        return [
            'estamentos' => Estamento::query()->orderBy('nombre')->get(),
            'cursos' => Curso::query()->orderBy('titulo')->get(),
            'sedes' => Sede::query()->orderBy('nombre')->get(),
        ];
    }

    /**
     * @return array{min: int, max: int}
     */
    public function ageBounds(): array
    {
        $youngestAcceptedBirthDate = Carbon::now()->subYears(100)->format('Y-m-d');
        $oldestAcceptedBirthDate = Carbon::now()->format('Y-m-d');

        $youngestBirthDate = User::whereNotNull('fecha_nacimiento')
            ->whereBetween('fecha_nacimiento', [$youngestAcceptedBirthDate, $oldestAcceptedBirthDate])
            ->max('fecha_nacimiento');

        $oldestBirthDate = User::whereNotNull('fecha_nacimiento')
            ->whereBetween('fecha_nacimiento', [$youngestAcceptedBirthDate, $oldestAcceptedBirthDate])
            ->min('fecha_nacimiento');

        return [
            'min' => $youngestBirthDate ? Carbon::parse($youngestBirthDate)->age : 18,
            'max' => $oldestBirthDate ? Carbon::parse($oldestBirthDate)->age : 80,
        ];
    }

    public function selectedCourse(ReportFilters $reportFilters): ?Curso
    {
        if (empty($reportFilters->courseIds)) {
            return null;
        }

        return Curso::withCount('modulos')->find($reportFilters->courseIds[0]);
    }

    /**
     * @param  array{min: int, max: int}  $ageBounds
     * @return Builder<User>
     */
    public function participants(ReportFilters $reportFilters, ?Curso $selectedCourse, array $ageBounds): Builder
    {
        $participantsQuery = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        if (! empty($reportFilters->estamentoIds)) {
            $participantsQuery->whereIn('estamento_id', $reportFilters->estamentoIds);
        }

        if (! empty($reportFilters->sedeIds)) {
            $participantsQuery->whereIn('sede_id', $reportFilters->sedeIds);
        }

        $this->applyCourseFilters($participantsQuery, $reportFilters, $selectedCourse);
        $this->applyAgeFilters($participantsQuery, $reportFilters, $ageBounds);
        $this->applyCertificateDateFilters($participantsQuery, $reportFilters);

        return $participantsQuery;
    }

    /**
     * @return array{selectedSedes: array<int, int>, selectedEstamentos: array<int, int>, selectedCursos: array<int, int>, edadActiva: true, estadoSeleccionado: string}
     */
    public function selectedState(ReportFilters $reportFilters): array
    {
        return [
            'selectedSedes' => $reportFilters->sedeIds,
            'selectedEstamentos' => $reportFilters->estamentoIds,
            'selectedCursos' => $reportFilters->courseIds,
            'edadActiva' => true,
            'estadoSeleccionado' => $reportFilters->estadoCapacitacion,
        ];
    }

    /**
     * @param  Builder<User>  $participantsQuery
     */
    private function applyCourseFilters(Builder $participantsQuery, ReportFilters $reportFilters, ?Curso $selectedCourse): void
    {
        if (! empty($reportFilters->courseIds) && ! $reportFilters->usesSingleCourseStatusContext()) {
            // El reporte multi-curso exige tener todos los certificados seleccionados.
            foreach ($reportFilters->courseIds as $courseId) {
                $participantsQuery->whereHas('certificados', function (Builder $certificateQuery) use ($courseId): void {
                    $certificateQuery->where('curso_id', $courseId);
                });
            }
        }

        if ($selectedCourse && count($reportFilters->courseIds) === 1) {
            $participantsQuery->withCount([
                'progresos as modulos_completados_count' => function (Builder $progressQuery) use ($selectedCourse): void {
                    $progressQuery->whereHas('modulo', function (Builder $moduleQuery) use ($selectedCourse): void {
                        $moduleQuery->where('curso_id', $selectedCourse->id);
                    })->where('completado', true);
                },
            ]);
        }

        if (! $reportFilters->usesSingleCourseStatusContext() || ! $selectedCourse) {
            return;
        }

        $courseId = $selectedCourse->id;
        $courseEstamentoIds = $selectedCourse->estamentos()->pluck('estamentos.id')->all();

        if (! empty($courseEstamentoIds)) {
            $participantsQuery->whereIn('estamento_id', $courseEstamentoIds);
        }

        match ($reportFilters->estadoCapacitacion) {
            'certificado' => $participantsQuery->whereHas('certificados', fn (Builder $certificateQuery) => $certificateQuery->where('curso_id', $courseId)),
            'en_progreso' => $participantsQuery
                ->whereHas('progresos', fn (Builder $progressQuery) => $progressQuery->whereHas('modulo', fn (Builder $moduleQuery) => $moduleQuery->where('curso_id', $courseId))->where('completado', true))
                ->whereDoesntHave('certificados', fn (Builder $certificateQuery) => $certificateQuery->where('curso_id', $courseId)),
            'no_iniciado' => $participantsQuery
                ->whereDoesntHave('progresos', fn (Builder $progressQuery) => $progressQuery->whereHas('modulo', fn (Builder $moduleQuery) => $moduleQuery->where('curso_id', $courseId)))
                ->whereDoesntHave('certificados', fn (Builder $certificateQuery) => $certificateQuery->where('curso_id', $courseId)),
            default => $participantsQuery,
        };
    }

    /**
     * @param  Builder<User>  $participantsQuery
     * @param  array{min: int, max: int}  $ageBounds
     */
    private function applyAgeFilters(Builder $participantsQuery, ReportFilters $reportFilters, array $ageBounds): void
    {
        $minimumAge = $reportFilters->edadMin ?? $ageBounds['min'];
        $maximumAge = $reportFilters->edadMax ?? $ageBounds['max'];

        $participantsQuery->where('fecha_nacimiento', '<=', Carbon::now()->subYears($minimumAge)->format('Y-m-d'));
        $participantsQuery->where('fecha_nacimiento', '>=', Carbon::now()->subYears($maximumAge + 1)->addDay()->format('Y-m-d'));
    }

    /**
     * @param  Builder<User>  $participantsQuery
     */
    private function applyCertificateDateFilters(Builder $participantsQuery, ReportFilters $reportFilters): void
    {
        if (! $reportFilters->fechaInicio || ! $reportFilters->fechaFin) {
            return;
        }

        $participantsQuery->whereHas('certificados', function (Builder $certificateQuery) use ($reportFilters): void {
            $certificateQuery->whereBetween('fecha_emision', [$reportFilters->fechaInicio, $reportFilters->fechaFin]);

            if (! empty($reportFilters->courseIds)) {
                $certificateQuery->whereIn('curso_id', $reportFilters->courseIds);
            }
        });
    }
}
