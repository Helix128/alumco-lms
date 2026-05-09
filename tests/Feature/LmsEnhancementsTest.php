<?php

namespace Tests\Feature;

use App\Livewire\Feedback\CourseFeedbackForm;
use App\Livewire\Feedback\PlatformFeedbackWidget;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Feedback;
use App\Models\Modulo;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class LmsEnhancementsTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_worker_can_save_course_and_platform_feedback(): void
    {
        [$trabajador, $curso] = $this->assignedWorkerAndCourse();
        Modulo::factory()->create(['curso_id' => $curso->id, 'orden' => 1]);

        Livewire::actingAs($trabajador)
            ->test(CourseFeedbackForm::class, ['curso' => $curso, 'progreso' => 100])
            ->set('rating', 5)
            ->set('categoria', 'utilidad')
            ->set('mensaje', 'Muy aplicable al trabajo diario.')
            ->call('guardar')
            ->assertSet('estado', 'Gracias. Tu feedback quedó registrado.');

        Livewire::actingAs($trabajador)
            ->test(PlatformFeedbackWidget::class)
            ->set('categoria', 'sugerencia')
            ->set('mensaje', 'Sería útil destacar los cursos próximos a vencer.')
            ->call('guardar')
            ->assertSet('estado', 'Feedback enviado al equipo de la plataforma.');

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $trabajador->id,
            'curso_id' => $curso->id,
            'tipo' => Feedback::TipoCurso,
            'rating' => 5,
        ]);
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $trabajador->id,
            'tipo' => Feedback::TipoPlataforma,
            'categoria' => 'sugerencia',
        ]);
    }

    public function test_worker_cannot_save_course_feedback_before_finishing_course(): void
    {
        [$trabajador, $curso] = $this->assignedWorkerAndCourse();

        Livewire::actingAs($trabajador)
            ->test(CourseFeedbackForm::class, ['curso' => $curso, 'progreso' => 80])
            ->set('rating', 5)
            ->set('categoria', 'utilidad')
            ->set('mensaje', 'El contenido será útil cuando termine la capacitación.')
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_developer_can_access_lms_health_page(): void
    {
        $dev = $this->createDev();

        $this->actingAs($dev)
            ->get(route('dev.salud-lms'))
            ->assertOk()
            ->assertSee('Salud operacional del LMS');
    }

    /**
     * @return array{0: User, 1: Curso, 2: User}
     */
    private function assignedWorkerAndCourse(): array
    {
        $sede = Sede::create(['nombre' => 'Hospital San Jose']);
        $estamento = Estamento::create(['nombre' => 'TENS']);
        $trabajador = $this->createTrabajador();
        $trabajador->update([
            'sede_id' => $sede->id,
            'estamento_id' => $estamento->id,
        ]);

        $capacitador = User::factory()->create();
        $capacitador->assignRole('Capacitador Interno');

        $curso = Curso::factory()->create(['capacitador_id' => $capacitador->id]);
        $curso->estamentos()->attach($estamento);
        PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addWeek()->toDateString(),
        ]);

        return [$trabajador, $curso, $capacitador];
    }
}
