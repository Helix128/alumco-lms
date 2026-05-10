<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BusinessIntelligenceDashboard extends Component
{
    private const COLOR_NEUTRAL = '#205099';

    private const COLOR_NEUTRAL_MUTED = '#8BA3C7';

    private const COLOR_POSITIVE = '#2F6F4E';

    private const COLOR_RISK = '#B3261E';

    private const COLOR_GRID = '#E7ECF3';

    private const ANNUAL_COVERAGE_TARGET = 85;

    public int $year;

    public string $sedeId = '';

    public string $estamentoId = '';

    public string $cursoId = '';

    public string $activeView = 'executive';

    /**
     * @var array<string, array{label: string, description: string}>
     */
    public array $availableViews = [
        'executive' => [
            'label' => 'Resumen',
            'description' => 'Cobertura y tendencia',
        ],
        'progress' => [
            'label' => 'Progreso',
            'description' => 'Avance y riesgo',
        ],
        'quality' => [
            'label' => 'Calidad',
            'description' => 'Evaluación y feedback',
        ],
        'segments' => [
            'label' => 'Segmentos',
            'description' => 'Sedes y usuarios',
        ],
    ];

    /**
     * @var array<int, int>
     */
    public array $years = [];

    /**
     * @var array<int, array{id: int, nombre: string}>
     */
    public array $sedes = [];

    /**
     * @var array<int, array{id: int, nombre: string}>
     */
    public array $estamentos = [];

    /**
     * @var array<int, array{id: int, titulo: string}>
     */
    public array $cursos = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);

        $this->year = now()->year;
        $this->loadFilterOptions();
    }

    public function resetFilters(): void
    {
        $this->year = now()->year;
        $this->sedeId = '';
        $this->estamentoId = '';
        $this->cursoId = '';

        $this->dispatch('bi-dashboard-render-charts');
    }

    public function setView(string $view): void
    {
        if (! array_key_exists($view, $this->availableViews)) {
            return;
        }

        $this->activeView = $view;

        $this->dispatch('bi-dashboard-render-charts');
    }

    public function updated(): void
    {
        $this->dispatch('bi-dashboard-render-charts');
    }

    public function render(): View
    {
        abort_unless(auth()->user()?->hasAdminAccess(), 403);

        $userIds = $this->filteredUserIds();
        $courseIds = $this->filteredCourseIds();
        $kpis = $this->buildKpis($userIds, $courseIds);
        $funnel = $this->buildFunnel($kpis);
        $sedeCoverage = $this->sedeCoverage($userIds, $courseIds);
        $estamentoCoverage = $this->estamentoCoverage($userIds, $courseIds);
        $criticalCourses = $this->criticalCourses($userIds, $courseIds);
        $charts = $this->buildCharts($userIds, $courseIds, $sedeCoverage, $criticalCourses);
        $rankings = $this->buildRankings($sedeCoverage, $estamentoCoverage, $criticalCourses);
        $collaborators = $this->collaboratorAnalytics($userIds, $courseIds);

        return view('livewire.admin.business-intelligence-dashboard', [
            'kpis' => $kpis,
            'contextKpis' => $this->contextKpis($kpis, $collaborators),
            'funnel' => $funnel,
            'charts' => $charts,
            'rankings' => $rankings,
            'collaborators' => $collaborators,
            'availableViews' => $this->availableViews,
            'activeView' => $this->activeView,
            'filterSignature' => md5((string) json_encode([
                $this->year,
                $this->sedeId,
                $this->estamentoId,
                $this->cursoId,
                $this->activeView,
                $kpis['updated_at'],
            ])),
        ]);
    }

    private function loadFilterOptions(): void
    {
        $currentYear = now()->year;
        $certificateYears = DB::table('certificados')
            ->whereNotNull('fecha_emision')
            ->selectRaw('YEAR(fecha_emision) as year')
            ->distinct()
            ->pluck('year');
        $planningYears = DB::table('planificaciones_cursos')
            ->whereNotNull('fecha_inicio')
            ->selectRaw('YEAR(fecha_inicio) as year')
            ->distinct()
            ->pluck('year');

        $this->years = collect([$currentYear])
            ->merge($certificateYears)
            ->merge($planningYears)
            ->filter()
            ->map(fn (mixed $year): int => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $this->sedes = DB::table('sedes')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (object $sede): array => ['id' => (int) $sede->id, 'nombre' => (string) $sede->nombre])
            ->all();

        $this->estamentos = DB::table('estamentos')
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (object $estamento): array => ['id' => (int) $estamento->id, 'nombre' => (string) $estamento->nombre])
            ->all();

        $this->cursos = DB::table('cursos')
            ->orderBy('titulo')
            ->get(['id', 'titulo'])
            ->map(fn (object $curso): array => ['id' => (int) $curso->id, 'titulo' => (string) $curso->titulo])
            ->all();
    }

    /**
     * @return Collection<int, int>
     */
    private function filteredUserIds(): Collection
    {
        $query = DB::table('users')
            ->join('model_has_roles', function (JoinClause $join): void {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Trabajador')
            ->where('activo', true)
            ->whereNull('deleted_at');

        if ($sedeId = $this->selectedSedeId()) {
            $query->where('sede_id', $sedeId);
        }

        if ($estamentoId = $this->selectedEstamentoId()) {
            $query->where('estamento_id', $estamentoId);
        }

        return $query->distinct()->pluck('users.id')->map(fn (mixed $id): int => (int) $id);
    }

    /**
     * @return Collection<int, int>
     */
    private function filteredCourseIds(): Collection
    {
        $query = DB::table('cursos')->select('cursos.id');

        if ($cursoId = $this->selectedCursoId()) {
            $query->where('cursos.id', $cursoId);
        }

        if ($estamentoId = $this->selectedEstamentoId()) {
            $query->join('curso_estamento', 'curso_estamento.curso_id', '=', 'cursos.id')
                ->where('curso_estamento.estamento_id', $estamentoId);
        }

        return $query->distinct()->pluck('cursos.id')->map(fn (mixed $id): int => (int) $id);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return array<string, mixed>
     */
    private function buildKpis(Collection $userIds, Collection $courseIds): array
    {
        $totalUsers = $userIds->count();
        $totalCourses = $courseIds->count();
        $plannedSessions = $this->withCourseScope(DB::table('planificaciones_cursos'), 'curso_id', $courseIds)
            ->whereYear('fecha_inicio', $this->year)
            ->when($this->selectedSedeId(), fn (Builder $query, int $sedeId): Builder => $query->where('sede_id', $sedeId))
            ->count();

        $totalCertificates = $this->certificateQuery($userIds, $courseIds)->count();
        $certifiedUsers = $this->certificateQuery($userIds, $courseIds)->distinct('user_id')->count('user_id');
        $startedUsers = $this->progressQuery($userIds, $courseIds)->distinct('progresos_modulo.user_id')->count('progresos_modulo.user_id');
        $completedModules = $this->progressQuery($userIds, $courseIds)->where('progresos_modulo.completado', true)->count();
        $totalProgressRows = $this->progressQuery($userIds, $courseIds)->count();
        $completionRate = $this->percentage($certifiedUsers, max($totalUsers, 1));
        $moduleCompletionRate = $this->percentage($completedModules, max($totalProgressRows, 1));
        $atRiskUsers = max($startedUsers - $certifiedUsers, 0);
        $avgFeedback = $this->feedbackQuery($userIds, $courseIds)->avg('rating');
        $feedbackCount = $this->feedbackQuery($userIds, $courseIds)->whereNotNull('rating')->count();
        $approvedAttempts = $this->evaluationAttemptsQuery($userIds, $courseIds)->where('intentos_evaluacion.aprobado', true)->count();
        $totalAttempts = $this->evaluationAttemptsQuery($userIds, $courseIds)->count();

        return [
            'active_users' => $totalUsers,
            'courses' => $totalCourses,
            'planned_sessions' => $plannedSessions,
            'certificates' => $totalCertificates,
            'certified_users' => $certifiedUsers,
            'started_users' => $startedUsers,
            'at_risk_users' => $atRiskUsers,
            'not_started_users' => max($totalUsers - $startedUsers, 0),
            'completion_rate' => $completionRate,
            'module_completion_rate' => $moduleCompletionRate,
            'feedback_average' => $avgFeedback ? round((float) $avgFeedback, 1) : null,
            'feedback_count' => $feedbackCount,
            'approval_rate' => $this->percentage($approvedAttempts, max($totalAttempts, 1)),
            'approved_attempts' => $approvedAttempts,
            'total_attempts' => $totalAttempts,
            'updated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param  array<string, mixed>  $kpis
     * @param  Collection<int, array<string, mixed>>  $collaborators
     * @return array<int, array{label: string, value: string, detail: string, tone: string}>
     */
    private function contextKpis(array $kpis, Collection $collaborators): array
    {
        $topCollaborator = $collaborators->sortByDesc('score')->first();
        $attentionCollaborators = $collaborators->where('status', 'Seguimiento')->count();

        return match ($this->activeView) {
            'progress' => [
                ['label' => 'Iniciaron formación', 'value' => number_format((int) $kpis['started_users']), 'detail' => 'Usuarios con actividad modular', 'tone' => 'neutral'],
                ['label' => 'Módulos completados', 'value' => $kpis['module_completion_rate'].'%', 'detail' => 'Ritmo sobre registros de avance', 'tone' => 'positive'],
                ['label' => 'Sin inicio visible', 'value' => number_format((int) $kpis['not_started_users']), 'detail' => 'Base activa sin progreso este año', 'tone' => 'warning'],
                ['label' => 'Requieren seguimiento', 'value' => number_format((int) $kpis['at_risk_users']), 'detail' => 'Iniciaron pero aún no certifican', 'tone' => ((int) $kpis['at_risk_users']) > 0 ? 'risk' : 'positive'],
            ],
            'quality' => [
                ['label' => 'Aprobación', 'value' => $kpis['approval_rate'].'%', 'detail' => number_format((int) $kpis['approved_attempts']).' de '.number_format((int) $kpis['total_attempts']).' intentos', 'tone' => 'positive'],
                ['label' => 'Satisfacción', 'value' => $kpis['feedback_average'] !== null ? (string) $kpis['feedback_average'] : 'Sin datos', 'detail' => number_format((int) $kpis['feedback_count']).' respuestas con rating', 'tone' => $kpis['feedback_average'] !== null ? 'neutral' : 'muted'],
                ['label' => 'Certificados emitidos', 'value' => number_format((int) $kpis['certificates']), 'detail' => 'Evidencias formales del periodo', 'tone' => 'neutral'],
                ['label' => 'Cursos con foco', 'value' => number_format((int) $kpis['courses']), 'detail' => 'Oferta incluida en los filtros', 'tone' => 'neutral'],
            ],
            'segments' => [
                ['label' => 'Base analizada', 'value' => number_format((int) $kpis['active_users']), 'detail' => 'Colaboradores activos filtrados', 'tone' => 'neutral'],
                ['label' => 'Cobertura segmentada', 'value' => $kpis['completion_rate'].'%', 'detail' => 'Personas certificadas en la base', 'tone' => 'positive'],
                ['label' => 'Mejor desempeño', 'value' => isset($topCollaborator['score']) ? $topCollaborator['score'].' pts' : 'Sin datos', 'detail' => $topCollaborator['name'] ?? 'Sin colaboradores con actividad', 'tone' => 'positive'],
                ['label' => 'Casos a revisar', 'value' => number_format($attentionCollaborators), 'detail' => 'Colaboradores iniciados sin certificación', 'tone' => $attentionCollaborators > 0 ? 'risk' : 'positive'],
            ],
            default => [
                ['label' => 'Cobertura anual', 'value' => $kpis['completion_rate'].'%', 'detail' => number_format((int) $kpis['certified_users']).' personas certificadas', 'tone' => ((int) $kpis['completion_rate']) >= self::ANNUAL_COVERAGE_TARGET ? 'positive' : 'risk'],
                ['label' => 'Base activa', 'value' => number_format((int) $kpis['active_users']), 'detail' => 'Colaboradores dentro del alcance', 'tone' => 'neutral'],
                ['label' => 'Planificaciones', 'value' => number_format((int) $kpis['planned_sessions']), 'detail' => 'Sesiones programadas en '.$this->year, 'tone' => 'neutral'],
                ['label' => 'Oferta vigente', 'value' => number_format((int) $kpis['courses']), 'detail' => 'Capacitaciones filtradas', 'tone' => 'neutral'],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $kpis
     * @return array<int, array{label: string, value: int, percentage: int, color: string}>
     */
    private function buildFunnel(array $kpis): array
    {
        $assigned = (int) $kpis['active_users'];
        $largest = max($assigned, (int) $kpis['started_users'], (int) $kpis['certified_users'], (int) $kpis['at_risk_users'], 1);

        return [
            [
                'label' => 'Base activa',
                'value' => $assigned,
                'percentage' => $this->percentage($assigned, $largest),
                'color' => 'bg-Alumco-blue',
            ],
            [
                'label' => 'Iniciaron formación',
                'value' => (int) $kpis['started_users'],
                'percentage' => $this->percentage((int) $kpis['started_users'], $largest),
                'color' => 'bg-Alumco-cyan',
            ],
            [
                'label' => 'Certificados',
                'value' => (int) $kpis['certified_users'],
                'percentage' => $this->percentage((int) $kpis['certified_users'], $largest),
                'color' => 'bg-Alumco-blue',
            ],
            [
                'label' => 'En riesgo',
                'value' => (int) $kpis['at_risk_users'],
                'percentage' => $this->percentage((int) $kpis['at_risk_users'], $largest),
                'color' => 'bg-red-800',
            ],
        ];
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @param  Collection<int, array{label: string, total: int, certified: int, percentage: int}>  $sedeCoverage
     * @param  Collection<int, array<string, mixed>>  $criticalCourses
     * @return array<string, array<string, mixed>>
     */
    private function buildCharts(Collection $userIds, Collection $courseIds, Collection $sedeCoverage, Collection $criticalCourses): array
    {
        $estamentoDistribution = $this->estamentoDistribution($userIds);
        $feedbackByCategory = $this->feedbackByCategory($userIds, $courseIds);
        $currentCertificateMonths = $this->monthlyCounts($this->certificateQueryForYear($userIds, $courseIds, $this->year), 'fecha_emision');

        return [
            'certificatesTrend' => $this->multiLineChart(
                'Certificados emitidos',
                [
                    [
                        'label' => (string) $this->year,
                        'data' => $currentCertificateMonths,
                        'color' => self::COLOR_NEUTRAL,
                    ],
                    [
                        'label' => (string) ($this->year - 1),
                        'data' => $this->monthlyCounts($this->certificateQueryForYear($userIds, $courseIds, $this->year - 1), 'fecha_emision'),
                        'color' => self::COLOR_NEUTRAL_MUTED,
                    ],
                    [
                        'label' => 'Promedio del ciclo',
                        'data' => $this->averageReferenceLine($currentCertificateMonths),
                        'color' => self::COLOR_POSITIVE,
                        'borderDash' => [6, 6],
                        'pointRadius' => 0,
                    ],
                ],
                'Mes',
                'Certificados',
                'Fuente: certificados emitidos'
            ),
            'planningTrend' => $this->barChart(
                'Cursos planificados',
                $this->monthlyCounts(
                    $this->withCourseScope(DB::table('planificaciones_cursos'), 'curso_id', $courseIds)
                        ->whereYear('fecha_inicio', $this->year)
                        ->when($this->selectedSedeId(), fn (Builder $query, int $sedeId): Builder => $query->where('sede_id', $sedeId)),
                    'fecha_inicio'
                ),
                self::COLOR_NEUTRAL,
                true,
                'Mes',
                'Planificaciones',
                'Fuente: planificaciones de cursos'
            ),
            'completionTrend' => $this->multiLineChart(
                'Inicio vs certificación',
                [
                    [
                        'label' => 'Usuarios que iniciaron',
                        'data' => $this->monthlyDistinctCounts(
                            $this->progressQuery($userIds, $courseIds),
                            'COALESCE(progresos_modulo.fecha_completado, progresos_modulo.created_at)',
                            'progresos_modulo.user_id'
                        ),
                        'color' => self::COLOR_NEUTRAL_MUTED,
                    ],
                    [
                        'label' => 'Usuarios certificados',
                        'data' => $this->monthlyDistinctCounts($this->certificateQuery($userIds, $courseIds), 'fecha_emision', 'user_id'),
                        'color' => self::COLOR_NEUTRAL,
                    ],
                ],
                'Mes',
                'Usuarios únicos',
                'Fuente: progreso modular y certificados'
            ),
            'sedeCoverage' => $this->horizontalBarChart('Cobertura por sede', $sedeCoverage, 'Sede', '% usuarios certificados'),
            'estamentoMix' => $this->horizontalValueChart('Usuarios por estamento', $estamentoDistribution, 'Usuarios activos', self::COLOR_NEUTRAL, 'Usuarios'),
            'evaluations' => [
                'type' => 'bar',
                'data' => [
                    'labels' => ['Aprobados', 'No aprobados'],
                    'datasets' => [[
                        'label' => 'Intentos',
                        'data' => $this->evaluationStatusCounts($userIds, $courseIds),
                        'backgroundColor' => [self::COLOR_POSITIVE, self::COLOR_RISK],
                        'borderRadius' => 4,
                    ]],
                ],
                'options' => $this->chartOptions('Resultado de evaluaciones', true, 'Resultado', 'Intentos', 'Fuente: intentos de evaluación'),
            ],
            'feedbackCategory' => $this->horizontalValueChart('Feedback por categoría', $feedbackByCategory, 'Rating promedio', self::COLOR_NEUTRAL, 'Promedio 1 a 5', 5),
            'progressFeedbackScatter' => $this->scatterChart('Cumplimiento vs feedback', $this->coursePerformanceScatter($criticalCourses)),
            'ageDistribution' => $this->barChart('Distribución etaria', $this->ageDistribution($userIds), self::COLOR_NEUTRAL_MUTED, false, 'Rango etario', 'Usuarios', 'Fuente: usuarios activos'),
        ];
    }

    /**
     * @param  Collection<int, array{label: string, total: int, certified: int, percentage: int}>  $sedeCoverage
     * @param  Collection<int, array{label: string, total: int, certified: int, percentage: int}>  $estamentoCoverage
     * @param  Collection<int, array<string, mixed>>  $criticalCourses
     * @return array<string, mixed>
     */
    private function buildRankings(Collection $sedeCoverage, Collection $estamentoCoverage, Collection $criticalCourses): array
    {
        return [
            'criticalCourses' => $criticalCourses,
            'topSedes' => $sedeCoverage->take(6),
            'topEstamentos' => $estamentoCoverage->take(6),
        ];
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     */
    private function certificateQuery(Collection $userIds, Collection $courseIds): Builder
    {
        return $this->certificateQueryForYear($userIds, $courseIds, $this->year);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     */
    private function certificateQueryForYear(Collection $userIds, Collection $courseIds, int $year): Builder
    {
        return $this->withUserScope(
            $this->withCourseScope(DB::table('certificados'), 'curso_id', $courseIds),
            'user_id',
            $userIds
        )->whereYear('fecha_emision', $year);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     */
    private function progressQuery(Collection $userIds, Collection $courseIds): Builder
    {
        return $this->withUserScope(
            $this->withCourseScope(
                DB::table('progresos_modulo')
                    ->join('modulos', 'modulos.id', '=', 'progresos_modulo.modulo_id'),
                'modulos.curso_id',
                $courseIds
            ),
            'progresos_modulo.user_id',
            $userIds
        )->whereYear(DB::raw('COALESCE(progresos_modulo.fecha_completado, progresos_modulo.created_at)'), $this->year);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     */
    private function feedbackQuery(Collection $userIds, Collection $courseIds): Builder
    {
        return $this->withUserScope(
            $this->withCourseScope(DB::table('feedbacks'), 'curso_id', $courseIds),
            'user_id',
            $userIds
        )->whereYear('created_at', $this->year);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     */
    private function evaluationAttemptsQuery(Collection $userIds, Collection $courseIds): Builder
    {
        return $this->withUserScope(
            $this->withCourseScope(
                DB::table('intentos_evaluacion')
                    ->join('evaluaciones', 'evaluaciones.id', '=', 'intentos_evaluacion.evaluacion_id')
                    ->join('modulos', 'modulos.id', '=', 'evaluaciones.modulo_id'),
                'modulos.curso_id',
                $courseIds
            ),
            'intentos_evaluacion.user_id',
            $userIds
        )->whereYear('intentos_evaluacion.created_at', $this->year);
    }

    /**
     * @param  Collection<int, int>  $ids
     */
    private function withUserScope(Builder $query, string $column, Collection $ids): Builder
    {
        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $ids->all());
    }

    /**
     * @param  Collection<int, int>  $ids
     */
    private function withCourseScope(Builder $query, string $column, Collection $ids): Builder
    {
        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $ids->all());
    }

    /**
     * @return array<int, int>
     */
    private function monthlyCounts(Builder $query, string $dateColumn): array
    {
        $counts = $query
            ->selectRaw("MONTH({$dateColumn}) as month, COUNT(*) as total")
            ->groupByRaw("MONTH({$dateColumn})")
            ->pluck('total', 'month');

        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [$month => (int) ($counts[$month] ?? 0)])
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function monthlyDistinctCounts(Builder $query, string $dateExpression, string $distinctColumn): array
    {
        $counts = $query
            ->selectRaw("MONTH({$dateExpression}) as month, COUNT(DISTINCT {$distinctColumn}) as total")
            ->groupByRaw("MONTH({$dateExpression})")
            ->pluck('total', 'month');

        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [$month => (int) ($counts[$month] ?? 0)])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return Collection<int, array{label: string, total: int, certified: int, percentage: int}>
     */
    private function sedeCoverage(Collection $userIds, Collection $courseIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        $certifiedBySede = $this->certificateQuery($userIds, $courseIds)
            ->join('users', 'users.id', '=', 'certificados.user_id')
            ->selectRaw('users.sede_id, COUNT(DISTINCT certificados.user_id) as total')
            ->groupBy('users.sede_id')
            ->pluck('total', 'sede_id');

        return DB::table('sedes')
            ->join('users', 'users.sede_id', '=', 'sedes.id')
            ->whereIn('users.id', $userIds->all())
            ->selectRaw('sedes.id, sedes.nombre, COUNT(DISTINCT users.id) as total')
            ->groupBy('sedes.id', 'sedes.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(function (object $row) use ($certifiedBySede): array {
                $certified = (int) ($certifiedBySede[$row->id] ?? 0);

                return [
                    'label' => (string) $row->nombre,
                    'total' => (int) $row->total,
                    'certified' => $certified,
                    'percentage' => $this->percentage($certified, max((int) $row->total, 1)),
                ];
            });
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return Collection<int, array{label: string, total: int, certified: int, percentage: int}>
     */
    private function estamentoCoverage(Collection $userIds, Collection $courseIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        $certifiedByEstamento = $this->certificateQuery($userIds, $courseIds)
            ->join('users', 'users.id', '=', 'certificados.user_id')
            ->selectRaw('users.estamento_id, COUNT(DISTINCT certificados.user_id) as total')
            ->groupBy('users.estamento_id')
            ->pluck('total', 'estamento_id');

        return DB::table('estamentos')
            ->join('users', 'users.estamento_id', '=', 'estamentos.id')
            ->whereIn('users.id', $userIds->all())
            ->selectRaw('estamentos.id, estamentos.nombre, COUNT(DISTINCT users.id) as total')
            ->groupBy('estamentos.id', 'estamentos.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(function (object $row) use ($certifiedByEstamento): array {
                $certified = (int) ($certifiedByEstamento[$row->id] ?? 0);

                return [
                    'label' => (string) $row->nombre,
                    'total' => (int) $row->total,
                    'certified' => $certified,
                    'percentage' => $this->percentage($certified, max((int) $row->total, 1)),
                ];
            });
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<string, int>
     */
    private function estamentoDistribution(Collection $userIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect(['Sin datos' => 0]);
        }

        return DB::table('estamentos')
            ->join('users', 'users.estamento_id', '=', 'estamentos.id')
            ->whereIn('users.id', $userIds->all())
            ->selectRaw('estamentos.nombre, COUNT(users.id) as total')
            ->groupBy('estamentos.nombre')
            ->orderByDesc('total')
            ->pluck('total', 'nombre')
            ->map(fn (mixed $total): int => (int) $total);
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return array<int, int>
     */
    private function evaluationStatusCounts(Collection $userIds, Collection $courseIds): array
    {
        $counts = $this->evaluationAttemptsQuery($userIds, $courseIds)
            ->selectRaw('aprobado, COUNT(*) as total')
            ->groupBy('aprobado')
            ->pluck('total', 'aprobado');

        return [(int) ($counts[1] ?? 0), (int) ($counts[0] ?? 0)];
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return Collection<string, float>
     */
    private function feedbackByCategory(Collection $userIds, Collection $courseIds): Collection
    {
        return $this->feedbackQuery($userIds, $courseIds)
            ->whereNotNull('rating')
            ->selectRaw('COALESCE(categoria, "General") as categoria, AVG(rating) as rating')
            ->groupBy('categoria')
            ->orderBy('categoria')
            ->pluck('rating', 'categoria')
            ->map(fn (mixed $rating): float => round((float) $rating, 1));
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return array<int, int>
     */
    private function ageDistribution(Collection $userIds): array
    {
        if ($userIds->isEmpty()) {
            return [0, 0, 0, 0, 0, 0];
        }

        $rows = DB::table('users')
            ->whereIn('id', $userIds->all())
            ->whereNotNull('fecha_nacimiento')
            ->selectRaw('
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 25 THEN "18-25"
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 26 AND 35 THEN "26-35"
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 36 AND 45 THEN "36-45"
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 46 AND 55 THEN "46-55"
                    WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 56 AND 65 THEN "56-65"
                    ELSE "66+"
                END as range_label,
                COUNT(*) as total
            ')
            ->groupBy('range_label')
            ->pluck('total', 'range_label');

        return collect(['18-25', '26-35', '36-45', '46-55', '56-65', '66+'])
            ->map(fn (string $range): int => (int) ($rows[$range] ?? 0))
            ->all();
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return Collection<int, array<string, mixed>>
     */
    private function criticalCourses(Collection $userIds, Collection $courseIds): Collection
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        $participants = DB::table('curso_estamento')
            ->join('users', 'users.estamento_id', '=', 'curso_estamento.estamento_id')
            ->whereIn('curso_estamento.curso_id', $courseIds->all())
            ->whereIn('users.id', $userIds->isEmpty() ? [-1] : $userIds->all())
            ->selectRaw('curso_estamento.curso_id, COUNT(DISTINCT users.id) as total')
            ->groupBy('curso_estamento.curso_id')
            ->pluck('total', 'curso_id');

        $started = $this->progressQuery($userIds, $courseIds)
            ->selectRaw('modulos.curso_id, COUNT(DISTINCT progresos_modulo.user_id) as total')
            ->groupBy('modulos.curso_id')
            ->pluck('total', 'curso_id');

        $certified = $this->certificateQuery($userIds, $courseIds)
            ->selectRaw('curso_id, COUNT(DISTINCT user_id) as total')
            ->groupBy('curso_id')
            ->pluck('total', 'curso_id');

        $ratings = $this->feedbackQuery($userIds, $courseIds)
            ->whereNotNull('rating')
            ->selectRaw('curso_id, AVG(rating) as rating')
            ->groupBy('curso_id')
            ->pluck('rating', 'curso_id');

        return DB::table('cursos')
            ->whereIn('id', $courseIds->all())
            ->orderBy('titulo')
            ->get(['id', 'titulo'])
            ->map(function (object $course) use ($participants, $started, $certified, $ratings): array {
                $assigned = (int) ($participants[$course->id] ?? 0);
                $startedCount = (int) ($started[$course->id] ?? 0);
                $certifiedCount = (int) ($certified[$course->id] ?? 0);
                $completion = $this->percentage($certifiedCount, max($assigned, 1));

                return [
                    'id' => (int) $course->id,
                    'title' => (string) $course->titulo,
                    'participants' => $assigned,
                    'started' => $startedCount,
                    'certified' => $certifiedCount,
                    'completion' => $completion,
                    'risk' => max($startedCount - $certifiedCount, 0),
                    'feedback' => isset($ratings[$course->id]) ? round((float) $ratings[$course->id], 1) : null,
                ];
            })
            ->filter(fn (array $course): bool => $course['participants'] > 0 || $course['started'] > 0 || $course['certified'] > 0)
            ->sortBy([
                ['completion', 'asc'],
                ['risk', 'desc'],
            ])
            ->take(8)
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $criticalCourses
     * @return Collection<int, array{x: int, y: float, label: string}>
     */
    private function coursePerformanceScatter(Collection $criticalCourses): Collection
    {
        return $criticalCourses
            ->filter(fn (array $course): bool => $course['feedback'] !== null)
            ->map(fn (array $course): array => [
                'x' => (int) $course['completion'],
                'y' => (float) $course['feedback'],
                'label' => (string) $course['title'],
            ])
            ->values();
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @param  Collection<int, int>  $courseIds
     * @return Collection<int, array<string, mixed>>
     */
    private function collaboratorAnalytics(Collection $userIds, Collection $courseIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        $certificates = $this->certificateQuery($userIds, $courseIds)
            ->selectRaw('user_id, COUNT(DISTINCT curso_id) as certified_courses, COUNT(*) as certificates')
            ->groupBy('user_id');

        $progress = $this->progressQuery($userIds, $courseIds)
            ->selectRaw('
                progresos_modulo.user_id,
                COUNT(DISTINCT modulos.curso_id) as started_courses,
                SUM(CASE WHEN progresos_modulo.completado = 1 THEN 1 ELSE 0 END) as completed_modules,
                COUNT(*) as progress_rows
            ')
            ->groupBy('progresos_modulo.user_id');

        $feedback = $this->feedbackQuery($userIds, $courseIds)
            ->whereNotNull('rating')
            ->selectRaw('user_id, AVG(rating) as feedback_average')
            ->groupBy('user_id');

        $attempts = $this->evaluationAttemptsQuery($userIds, $courseIds)
            ->selectRaw('
                intentos_evaluacion.user_id,
                SUM(CASE WHEN intentos_evaluacion.aprobado = 1 THEN 1 ELSE 0 END) as approved_attempts,
                COUNT(*) as total_attempts
            ')
            ->groupBy('intentos_evaluacion.user_id');

        $assignedCourses = $this->withCourseScope(
            DB::table('users')
                ->leftJoin('curso_estamento', 'curso_estamento.estamento_id', '=', 'users.estamento_id')
                ->whereIn('users.id', $userIds->all())
                ->selectRaw('users.id as user_id, COUNT(DISTINCT curso_estamento.curso_id) as assigned_courses')
                ->groupBy('users.id'),
            'curso_estamento.curso_id',
            $courseIds
        );

        return DB::table('users')
            ->leftJoin('sedes', 'sedes.id', '=', 'users.sede_id')
            ->leftJoin('estamentos', 'estamentos.id', '=', 'users.estamento_id')
            ->leftJoinSub($assignedCourses, 'assigned_courses', 'assigned_courses.user_id', '=', 'users.id')
            ->leftJoinSub($certificates, 'certificates', 'certificates.user_id', '=', 'users.id')
            ->leftJoinSub($progress, 'progress', 'progress.user_id', '=', 'users.id')
            ->leftJoinSub($feedback, 'feedback', 'feedback.user_id', '=', 'users.id')
            ->leftJoinSub($attempts, 'attempts', 'attempts.user_id', '=', 'users.id')
            ->whereIn('users.id', $userIds->all())
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.email',
                'sedes.nombre as sede',
                'estamentos.nombre as estamento',
                'assigned_courses.assigned_courses',
                'certificates.certified_courses',
                'certificates.certificates',
                'progress.started_courses',
                'progress.completed_modules',
                'progress.progress_rows',
                'feedback.feedback_average',
                'attempts.approved_attempts',
                'attempts.total_attempts',
            ])
            ->map(function (object $user): array {
                $assignedCourses = (int) ($user->assigned_courses ?? 0);
                $startedCourses = (int) ($user->started_courses ?? 0);
                $certifiedCourses = (int) ($user->certified_courses ?? 0);
                $progressRows = (int) ($user->progress_rows ?? 0);
                $completedModules = (int) ($user->completed_modules ?? 0);
                $approvedAttempts = (int) ($user->approved_attempts ?? 0);
                $totalAttempts = (int) ($user->total_attempts ?? 0);
                $coverage = $this->percentage($certifiedCourses, max($assignedCourses, 1));
                $moduleProgress = $this->percentage($completedModules, max($progressRows, 1));
                $approval = $this->percentage($approvedAttempts, max($totalAttempts, 1));
                $feedbackAverage = $user->feedback_average !== null ? round((float) $user->feedback_average, 1) : null;
                $score = (int) round(($coverage * 0.45) + ($moduleProgress * 0.25) + ($approval * 0.2) + (($feedbackAverage ?? 0) * 2));

                return [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'sede' => $user->sede ? (string) $user->sede : 'Sin sede',
                    'estamento' => $user->estamento ? (string) $user->estamento : 'Sin estamento',
                    'assigned_courses' => $assignedCourses,
                    'started_courses' => $startedCourses,
                    'certified_courses' => $certifiedCourses,
                    'certificates' => (int) ($user->certificates ?? 0),
                    'coverage' => $coverage,
                    'module_progress' => $moduleProgress,
                    'approval' => $approval,
                    'feedback_average' => $feedbackAverage,
                    'score' => $score,
                    'status' => $startedCourses > $certifiedCourses ? 'Seguimiento' : ($certifiedCourses > 0 ? 'Al día' : 'Sin inicio'),
                ];
            })
            ->sortBy(fn (array $collaborator): string => sprintf(
                '%d-%03d-%s',
                ['Seguimiento' => 0, 'Sin inicio' => 1, 'Al día' => 2][$collaborator['status']] ?? 3,
                $collaborator['score'],
                $collaborator['name'],
            ))
            ->take(12)
            ->values();
    }

    /**
     * @param  array<int, array{label: string, data: array<int, int>, color: string, borderDash?: array<int, int>, pointRadius?: int}>  $datasets
     * @return array<string, mixed>
     */
    private function multiLineChart(string $label, array $datasets, string $xTitle, string $yTitle, string $source): array
    {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $this->monthLabels(),
                'datasets' => collect($datasets)
                    ->map(fn (array $dataset): array => [
                        'label' => $dataset['label'],
                        'data' => $dataset['data'],
                        'borderColor' => $dataset['color'],
                        'backgroundColor' => $dataset['color'].'22',
                        'borderWidth' => 2,
                        'pointRadius' => $dataset['pointRadius'] ?? 3,
                        'tension' => 0.25,
                        'fill' => false,
                        'borderDash' => $dataset['borderDash'] ?? [],
                    ])
                    ->all(),
            ],
            'options' => $this->chartOptions($label, true, $xTitle, $yTitle, $source),
        ];
    }

    /**
     * @param  array<int, int>  $values
     * @return array<string, mixed>
     */
    private function barChart(
        string $label,
        array $values,
        string $color,
        bool $monthly = true,
        string $xTitle = '',
        string $yTitle = '',
        string $source = ''
    ): array {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $monthly ? $this->monthLabels() : ['18-25', '26-35', '36-45', '46-55', '56-65', '66+'],
                'datasets' => [[
                    'label' => $label,
                    'data' => $values,
                    'backgroundColor' => $color,
                    'borderRadius' => 4,
                ]],
            ],
            'options' => $this->chartOptions($label, true, $xTitle, $yTitle, $source),
        ];
    }

    /**
     * @param  Collection<int, array{label: string, total: int, certified: int, percentage: int}>  $rows
     * @return array<string, mixed>
     */
    private function horizontalBarChart(string $label, Collection $rows, string $yTitle, string $xTitle): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $rows->pluck('label')->values()->all(),
                'datasets' => [[
                    'label' => '% cobertura',
                    'data' => $rows->pluck('percentage')->values()->all(),
                    'backgroundColor' => self::COLOR_NEUTRAL,
                    'borderRadius' => 4,
                ]],
            ],
            'options' => array_replace_recursive($this->chartOptions($label, true, $xTitle, $yTitle, 'Fuente: usuarios activos y certificados'), [
                'indexAxis' => 'y',
                'scales' => [
                    'x' => ['max' => 100],
                ],
            ]),
        ];
    }

    /**
     * @param  Collection<string, int|float>  $values
     * @return array<string, mixed>
     */
    private function horizontalValueChart(string $label, Collection $values, string $datasetLabel, string $color, string $xTitle, ?int $max = null): array
    {
        if ($values->isEmpty()) {
            $values = collect(['Sin datos' => 0]);
        }

        $options = array_replace_recursive($this->chartOptions($label, true, $xTitle, 'Categoría', 'Fuente: datos operacionales Alumco'), [
            'indexAxis' => 'y',
        ]);

        if ($max !== null) {
            $options = array_replace_recursive($options, [
                'scales' => [
                    'x' => ['max' => $max],
                ],
            ]);
        }

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $values->keys()->all(),
                'datasets' => [[
                    'label' => $datasetLabel,
                    'data' => $values->values()->all(),
                    'backgroundColor' => $color,
                    'borderRadius' => 4,
                ]],
            ],
            'options' => $options,
        ];
    }

    /**
     * @param  Collection<int, array{x: int, y: float, label: string}>  $values
     * @return array<string, mixed>
     */
    private function scatterChart(string $label, Collection $values): array
    {
        return [
            'type' => 'scatter',
            'data' => [
                'datasets' => [[
                    'label' => 'Curso',
                    'data' => $values->all(),
                    'backgroundColor' => self::COLOR_NEUTRAL,
                    'borderColor' => self::COLOR_NEUTRAL,
                    'pointRadius' => 5,
                ]],
            ],
            'options' => array_replace_recursive($this->chartOptions($label, true, '% cumplimiento', 'Feedback promedio', 'Fuente: cursos, certificados y feedback'), [
                'scales' => [
                    'x' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'y' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function chartOptions(string $title, bool $cartesian = true, string $xTitle = '', string $yTitle = '', string $source = ''): array
    {
        $options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 10,
                        'usePointStyle' => true,
                    ],
                ],
                'title' => [
                    'display' => false,
                    'text' => $title,
                ],
                'tooltip' => [
                    'intersect' => false,
                    'mode' => 'nearest',
                ],
            ],
        ];

        if (! $cartesian) {
            return $options;
        }

        $options['scales'] = [
            'x' => [
                'grid' => ['display' => false],
                'title' => [
                    'display' => $xTitle !== '',
                    'text' => $xTitle,
                ],
            ],
            'y' => [
                'beginAtZero' => true,
                'grid' => ['color' => self::COLOR_GRID],
                'title' => [
                    'display' => $yTitle !== '',
                    'text' => $yTitle,
                ],
            ],
        ];

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private function monthLabels(): array
    {
        return ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    }

    private function percentage(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    /**
     * @param  array<int, int>  $values
     * @return array<int, float>
     */
    private function averageReferenceLine(array $values): array
    {
        $activeValues = collect($values)->filter(fn (int $value): bool => $value > 0);
        $average = $activeValues->isEmpty() ? 0 : round((float) $activeValues->average(), 1);

        return array_fill(0, 12, $average);
    }

    private function selectedSedeId(): ?int
    {
        return $this->sedeId !== '' ? (int) $this->sedeId : null;
    }

    private function selectedEstamentoId(): ?int
    {
        return $this->estamentoId !== '' ? (int) $this->estamentoId : null;
    }

    private function selectedCursoId(): ?int
    {
        return $this->cursoId !== '' ? (int) $this->cursoId : null;
    }
}
