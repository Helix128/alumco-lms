<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Modulo;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\ProgresoModulo;
use App\Models\Sede;
use App\Models\User;
use App\Notifications\CourseAvailableNotification;
use App\Notifications\CourseCompletedCertificateNotification;
use App\Notifications\CourseDeadlineReminderNotification;
use App\Services\CertificadoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_generation_notifies_worker_once_with_authenticated_download_link(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $curso = Curso::factory()->create();

        $certificado = app(CertificadoService::class)->generarParaUsuario($user, $curso);
        app(CertificadoService::class)->generarParaUsuario($user, $curso);

        $sent = Notification::sent($user, CourseCompletedCertificateNotification::class);

        $this->assertCount(1, $sent);
        $this->assertSame(
            route('mis-certificados.descargar', $certificado),
            $sent->first()->toArray($user)['download_url']
        );
        $this->assertSame(1, NotificationDelivery::where('type', NotificationDelivery::CourseCompletedCertificate)->count());
    }

    public function test_available_course_command_notifies_only_eligible_workers_once(): void
    {
        Notification::fake();
        $this->travelTo(Carbon::create(2026, 5, 3, 10, 0, 0, 'America/Santiago'));

        [$curso, $planificacion, $worker, $ineligibleWorker] = $this->createPlannedCourseForWorkers();

        $this->artisan('lms:send-course-available-notifications')
            ->assertExitCode(0);
        $this->artisan('lms:send-course-available-notifications')
            ->assertExitCode(0);

        $sent = Notification::sent($worker, CourseAvailableNotification::class);

        $this->assertCount(1, $sent);
        $this->assertSame(
            route('cursos.show', $curso),
            $sent->first()->toArray($worker)['course_url']
        );
        $this->assertSame($planificacion->id, $sent->first()->planificacion->id);
        Notification::assertNotSentTo($ineligibleWorker, CourseAvailableNotification::class);
        $this->assertSame(1, NotificationDelivery::where('type', NotificationDelivery::CourseAvailable)->count());
    }

    public function test_deadline_reminder_command_notifies_workers_with_less_than_half_progress_once(): void
    {
        Notification::fake();
        $this->travelTo(Carbon::create(2026, 5, 3, 10, 0, 0, 'America/Santiago'));

        [$curso, $planificacion, $worker, $ineligibleWorker] = $this->createPlannedCourseForWorkers([
            'fecha_inicio' => now('America/Santiago')->subDays(5),
            'fecha_fin' => now('America/Santiago')->addDays(2),
        ]);
        $highProgressWorker = $this->createWorker($worker->estamento, $worker->sede);

        $modules = Modulo::factory()
            ->count(4)
            ->sequence(
                ['orden' => 1],
                ['orden' => 2],
                ['orden' => 3],
                ['orden' => 4],
            )
            ->create(['curso_id' => $curso->id]);

        ProgresoModulo::create([
            'user_id' => $worker->id,
            'modulo_id' => $modules[0]->id,
            'completado' => true,
            'fecha_completado' => now(),
        ]);

        foreach ($modules->take(2) as $module) {
            ProgresoModulo::create([
                'user_id' => $highProgressWorker->id,
                'modulo_id' => $module->id,
                'completado' => true,
                'fecha_completado' => now(),
            ]);
        }

        $this->artisan('lms:send-course-deadline-reminders')
            ->assertExitCode(0);
        $this->artisan('lms:send-course-deadline-reminders')
            ->assertExitCode(0);

        $sent = Notification::sent($worker, CourseDeadlineReminderNotification::class);

        $this->assertCount(1, $sent);
        $this->assertSame(25, $sent->first()->progreso);
        $this->assertSame(
            route('cursos.show', $curso),
            $sent->first()->toArray($worker)['course_url']
        );
        $this->assertSame($planificacion->id, $sent->first()->planificacion->id);
        Notification::assertNotSentTo($highProgressWorker, CourseDeadlineReminderNotification::class);
        Notification::assertNotSentTo($ineligibleWorker, CourseDeadlineReminderNotification::class);
        $this->assertSame(1, NotificationDelivery::where('type', NotificationDelivery::CourseDeadlineReminder)->count());
    }

    /**
     * @param  array{fecha_inicio?: Carbon, fecha_fin?: Carbon}  $planningOverrides
     * @return array{Curso, PlanificacionCurso, User, User}
     */
    private function createPlannedCourseForWorkers(array $planningOverrides = []): array
    {
        Role::firstOrCreate(['name' => 'Trabajador']);

        $sede = Sede::create(['nombre' => 'Sede Central']);
        $otraSede = Sede::create(['nombre' => 'Sede Norte']);
        $estamento = Estamento::create(['nombre' => 'Operaciones']);
        $otroEstamento = Estamento::create(['nombre' => 'Administración']);

        $worker = $this->createWorker($estamento, $sede);
        $ineligibleWorker = $this->createWorker($otroEstamento, $otraSede);
        $curso = Curso::factory()->create();
        $curso->estamentos()->attach($estamento);

        $planificacion = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => $planningOverrides['fecha_inicio'] ?? now('America/Santiago')->startOfDay(),
            'fecha_fin' => $planningOverrides['fecha_fin'] ?? now('America/Santiago')->addDays(5),
        ]);

        return [$curso, $planificacion, $worker, $ineligibleWorker];
    }

    private function createWorker(Estamento $estamento, Sede $sede): User
    {
        $worker = User::factory()->create([
            'activo' => true,
            'estamento_id' => $estamento->id,
            'sede_id' => $sede->id,
        ]);
        $worker->assignRole('Trabajador');

        return $worker;
    }
}
