<?php

namespace Tests\Feature;

use App\Livewire\VerEvaluacion;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\NotificationDelivery;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\ProgresoModulo;
use App\Models\Sede;
use App\Models\User;
use App\Notifications\CourseCompletedCertificateNotification;
use App\Services\Certificados\CertificateEligibility;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CertificateAutoIssuanceTest extends TestCase
{
    use RefreshDatabase;

    private User $worker;

    private Estamento $estamento;

    private Sede $sede;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->sede = Sede::create(['nombre' => 'Sede de pruebas']);
        $this->estamento = Estamento::create(['nombre' => 'Estamento de pruebas']);
        $this->worker = User::factory()->create([
            'activo' => true,
            'sede_id' => $this->sede->id,
            'estamento_id' => $this->estamento->id,
        ]);
        $this->worker->assignRole('Trabajador');
    }

    public function test_each_content_module_type_issues_a_certificate_when_it_is_last(): void
    {
        Notification::fake();

        foreach (['video', 'pdf', 'ppt', 'texto', 'imagen'] as $type) {
            $course = $this->createAssignedCourse();
            $module = Modulo::factory()->create([
                'curso_id' => $course->id,
                'orden' => 1,
                'tipo_contenido' => $type,
            ]);

            $this->actingAs($this->worker)
                ->post(route('modulos.completar', [$course, $module]), ['action' => 'next'])
                ->assertRedirect(route('cursos.show', $course));

            $this->assertDatabaseHas('certificados', [
                'user_id' => $this->worker->id,
                'curso_id' => $course->id,
            ]);
        }
    }

    public function test_approved_intermediate_evaluation_waits_for_final_content_module(): void
    {
        Notification::fake();

        $course = $this->createAssignedCourse();
        [$evaluationModule, $question, $correctOption] = $this->createEvaluationModule($course, 1);
        $lastModule = Modulo::factory()->texto()->create([
            'curso_id' => $course->id,
            'orden' => 2,
        ]);

        Livewire::actingAs($this->worker)
            ->test(VerEvaluacion::class, ['modulo' => $evaluationModule, 'curso' => $course])
            ->set("respuestasSeleccionadas.{$question->id}", $correctOption->id)
            ->call('finalizar')
            ->assertSet('aprobado', true)
            ->assertSet('certificadoGenerado', false);

        $this->assertDatabaseMissing('certificados', [
            'user_id' => $this->worker->id,
            'curso_id' => $course->id,
        ]);

        $this->actingAs($this->worker)
            ->post(route('modulos.completar', [$course, $lastModule]), ['action' => 'next'])
            ->assertRedirect(route('cursos.show', $course));

        $this->assertDatabaseHas('certificados', [
            'user_id' => $this->worker->id,
            'curso_id' => $course->id,
        ]);
    }

    public function test_completing_a_module_before_the_last_does_not_issue_a_certificate(): void
    {
        Notification::fake();

        $course = $this->createAssignedCourse();
        $firstModule = Modulo::factory()->video()->create([
            'curso_id' => $course->id,
            'orden' => 1,
        ]);
        $lastModule = Modulo::factory()->pdf()->create([
            'curso_id' => $course->id,
            'orden' => 2,
        ]);

        $this->actingAs($this->worker)
            ->post(route('modulos.completar', [$course, $firstModule]), ['action' => 'next'])
            ->assertRedirect(route('modulos.show', [$course, $lastModule]));

        $this->assertDatabaseMissing('certificados', [
            'user_id' => $this->worker->id,
            'curso_id' => $course->id,
        ]);
    }

    public function test_both_completion_actions_issue_a_certificate_at_full_progress(): void
    {
        Notification::fake();

        foreach (['next', 'course'] as $action) {
            $course = $this->createAssignedCourse();
            $module = Modulo::factory()->imagen()->create([
                'curso_id' => $course->id,
                'orden' => 1,
            ]);

            $this->actingAs($this->worker)
                ->post(route('modulos.completar', [$course, $module]), ['action' => $action])
                ->assertRedirect(route('cursos.show', $course));

            $this->assertDatabaseHas('certificados', [
                'user_id' => $this->worker->id,
                'curso_id' => $course->id,
            ]);
        }
    }

    public function test_retrying_completion_does_not_duplicate_certificate_or_notification(): void
    {
        Notification::fake();

        $course = $this->createAssignedCourse();
        $module = Modulo::factory()->pdf()->create([
            'curso_id' => $course->id,
            'orden' => 1,
        ]);

        foreach (['next', 'course'] as $action) {
            $this->actingAs($this->worker)
                ->post(route('modulos.completar', [$course, $module]), ['action' => $action])
                ->assertRedirect(route('cursos.show', $course));
        }

        $this->assertSame(1, Certificado::where('user_id', $this->worker->id)
            ->where('curso_id', $course->id)
            ->count());
        $this->assertSame(1, NotificationDelivery::where('user_id', $this->worker->id)
            ->where('curso_id', $course->id)
            ->where('type', NotificationDelivery::CourseCompletedCertificate)
            ->count());
        Notification::assertSentToTimes($this->worker, CourseCompletedCertificateNotification::class, 1);
    }

    public function test_approved_evaluation_as_last_module_still_issues_a_certificate(): void
    {
        Notification::fake();

        $course = $this->createAssignedCourse();
        $firstModule = Modulo::factory()->video()->create([
            'curso_id' => $course->id,
            'orden' => 1,
        ]);
        [$evaluationModule, $question, $correctOption] = $this->createEvaluationModule($course, 2);

        ProgresoModulo::create([
            'user_id' => $this->worker->id,
            'modulo_id' => $firstModule->id,
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        Livewire::actingAs($this->worker)
            ->test(VerEvaluacion::class, ['modulo' => $evaluationModule, 'curso' => $course])
            ->set("respuestasSeleccionadas.{$question->id}", $correctOption->id)
            ->call('finalizar')
            ->assertSet('aprobado', true)
            ->assertSet('certificadoGenerado', true);

        $this->assertDatabaseHas('certificados', [
            'user_id' => $this->worker->id,
            'curso_id' => $course->id,
        ]);
    }

    public function test_module_completion_remains_successful_if_certificate_issuance_fails(): void
    {
        Notification::fake();

        $eligibility = Mockery::mock(CertificateEligibility::class);
        $eligibility->shouldReceive('ensure')
            ->once()
            ->andThrow(new RuntimeException('Certificate service unavailable'));
        $this->app->instance(CertificateEligibility::class, $eligibility);

        $course = $this->createAssignedCourse();
        $module = Modulo::factory()->texto()->create([
            'curso_id' => $course->id,
            'orden' => 1,
        ]);

        $this->actingAs($this->worker)
            ->post(route('modulos.completar', [$course, $module]), ['action' => 'next'])
            ->assertRedirect(route('cursos.show', $course));

        $this->assertDatabaseHas('progresos_modulo', [
            'user_id' => $this->worker->id,
            'modulo_id' => $module->id,
            'completado' => true,
        ]);
        $this->assertDatabaseMissing('certificados', [
            'user_id' => $this->worker->id,
            'curso_id' => $course->id,
        ]);
    }

    private function createAssignedCourse(): Curso
    {
        $course = Curso::factory()->create();
        $course->estamentos()->attach($this->estamento);

        PlanificacionCurso::create([
            'curso_id' => $course->id,
            'sede_id' => $this->sede->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addWeek()->toDateString(),
        ]);

        return $course;
    }

    /**
     * @return array{Modulo, Pregunta, Opcion}
     */
    private function createEvaluationModule(Curso $course, int $order): array
    {
        $module = Modulo::factory()->evaluacion()->create([
            'curso_id' => $course->id,
            'orden' => $order,
        ]);
        $evaluation = Evaluacion::factory()->create(['modulo_id' => $module->id]);
        $question = Pregunta::factory()->create([
            'evaluacion_id' => $evaluation->id,
            'orden' => 1,
        ]);
        $correctOption = Opcion::factory()->correcta()->create([
            'pregunta_id' => $question->id,
            'orden' => 1,
        ]);

        return [$module, $question, $correctOption];
    }
}
