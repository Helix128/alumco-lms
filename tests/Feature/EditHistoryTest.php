<?php

namespace Tests\Feature;

use App\Exceptions\EditHistoryConflict;
use App\Models\Curso;
use App\Models\EditHistory;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\ReportePreset;
use App\Models\SeccionCurso;
use App\Services\History\EditHistoryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class EditHistoryTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private EditHistoryService $history;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->history = app(EditHistoryService::class);
    }

    public function test_course_structure_supports_complete_undo_and_redo_cycles(): void
    {
        $admin = $this->createAdmin();
        $course = Curso::factory()->create();

        $this->history->captureChange(
            $admin,
            EditHistoryService::CourseStructure,
            $course->id,
            'Crear sección de bienvenida',
            fn () => SeccionCurso::create(['curso_id' => $course->id, 'titulo' => 'Bienvenida', 'orden' => 1]),
        );

        $this->assertDatabaseHas('seccion_cursos', ['curso_id' => $course->id, 'deleted_at' => null]);
        $this->history->undo($admin, EditHistoryService::CourseStructure, $course->id);
        $this->assertSoftDeleted('seccion_cursos', ['curso_id' => $course->id]);
        $this->history->redo($admin, EditHistoryService::CourseStructure, $course->id);
        $this->assertDatabaseHas('seccion_cursos', ['curso_id' => $course->id, 'titulo' => 'Bienvenida', 'deleted_at' => null]);
    }

    public function test_undo_refuses_to_overwrite_a_later_change_from_another_person(): void
    {
        $admin = $this->createAdmin();
        $course = Curso::factory()->create();
        $section = SeccionCurso::create(['curso_id' => $course->id, 'titulo' => 'Inicio', 'orden' => 1]);

        $this->history->captureChange(
            $admin,
            EditHistoryService::CourseStructure,
            $course->id,
            'Renombrar sección',
            fn () => $section->update(['titulo' => 'Introducción']),
        );

        $section->update(['titulo' => 'Cambio concurrente']);

        $this->expectException(EditHistoryConflict::class);
        try {
            $this->history->undo($admin, EditHistoryService::CourseStructure, $course->id);
        } finally {
            $this->assertDatabaseHas('seccion_cursos', ['id' => $section->id, 'titulo' => 'Cambio concurrente']);
        }
    }

    public function test_evaluation_calendar_and_report_contexts_are_recoverable(): void
    {
        $admin = $this->createAdmin();
        $course = Curso::factory()->create();
        $module = Modulo::factory()->evaluacion()->create(['curso_id' => $course->id]);
        $evaluation = Evaluacion::factory()->create(['modulo_id' => $module->id]);

        $this->history->captureChange(
            $admin,
            EditHistoryService::Evaluation,
            $evaluation->id,
            'Agregar pregunta',
            fn () => Pregunta::factory()->create(['evaluacion_id' => $evaluation->id, 'enunciado' => '¿Pregunta?', 'orden' => 1]),
        );
        $this->history->undo($admin, EditHistoryService::Evaluation, $evaluation->id);
        $this->assertSame(0, Pregunta::where('evaluacion_id', $evaluation->id)->count());

        $this->history->captureChange(
            $admin,
            EditHistoryService::Calendar,
            EditHistoryService::GlobalScope,
            'Programar capacitación',
            fn () => PlanificacionCurso::create([
                'curso_id' => $course->id,
                'fecha_inicio' => now()->addMonth()->toDateString(),
                'fecha_fin' => now()->addMonth()->addDay()->toDateString(),
            ]),
        );
        $this->history->undo($admin, EditHistoryService::Calendar, EditHistoryService::GlobalScope);
        $this->assertSame(0, PlanificacionCurso::count());

        $this->history->captureChange(
            $admin,
            EditHistoryService::Reports,
            EditHistoryService::GlobalScope,
            'Guardar formato',
            fn () => ReportePreset::create(['nombre' => 'Mensual', 'columnas' => ['nombre', 'progreso']]),
        );
        $this->history->undo($admin, EditHistoryService::Reports, EditHistoryService::GlobalScope);
        $this->assertSame(0, ReportePreset::count());
    }

    public function test_history_keeps_twenty_steps_for_thirty_minutes_and_a_new_action_clears_redo(): void
    {
        $admin = $this->createAdmin();
        $course = Curso::factory()->create();

        for ($step = 1; $step <= 22; $step++) {
            $this->history->captureChange(
                $admin,
                EditHistoryService::CourseStructure,
                $course->id,
                "Cambio {$step}",
                fn () => $course->update(['titulo' => "Capacitación {$step}"]),
            );
        }

        $this->assertSame(20, EditHistory::where('user_id', $admin->id)->count());
        $this->assertTrue(EditHistory::where('user_id', $admin->id)->get()->every(
            fn (EditHistory $step): bool => $step->expires_at->between(now()->addMinutes(29), now()->addMinutes(31))
        ));

        $this->history->undo($admin, EditHistoryService::CourseStructure, $course->id);
        $course->refresh();
        $this->history->captureChange(
            $admin,
            EditHistoryService::CourseStructure,
            $course->id,
            'Cambio alternativo',
            fn () => $course->update(['titulo' => 'Versión alternativa']),
        );

        $availability = $this->history->availability($admin, EditHistoryService::CourseStructure, $course->id);
        $this->assertFalse($availability['can_redo']);
    }

    public function test_soft_deleted_course_is_purged_only_after_thirty_days(): void
    {
        $course = Curso::factory()->create();
        $course->delete();
        $this->artisan('lms:purge-deleted-content')->assertSuccessful();
        $this->assertDatabaseHas('cursos', ['id' => $course->id]);

        Curso::withTrashed()->whereKey($course->id)->update(['deleted_at' => now()->subDays(31)]);
        $this->artisan('lms:purge-deleted-content')->assertSuccessful();
        $this->assertDatabaseMissing('cursos', ['id' => $course->id]);
    }
}
