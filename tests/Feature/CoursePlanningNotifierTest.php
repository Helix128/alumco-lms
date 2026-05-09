<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\NotificationDelivery;
use App\Models\PlanificacionCurso;
use App\Models\Sede;
use App\Models\User;
use App\Notifications\CoursePlanningNotification;
use App\Services\Cursos\CoursePlanningNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CoursePlanningNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_eligible_workers_once_for_future_scheduled_planning(): void
    {
        Notification::fake();

        $context = $this->createPlanningContext(now('America/Santiago')->addDays(10));

        app(CoursePlanningNotifier::class)->notifyScheduled($context['planificacion']);
        app(CoursePlanningNotifier::class)->notifyScheduled($context['planificacion']);

        Notification::assertSentTo(
            $context['eligibleWorker'],
            CoursePlanningNotification::class,
            fn (CoursePlanningNotification $notification): bool => $notification->type === 'scheduled'
        );
        Notification::assertNotSentTo($context['ineligibleWorker'], CoursePlanningNotification::class);
        $this->assertSame(1, NotificationDelivery::where('type', NotificationDelivery::CoursePlanningScheduled)->count());
    }

    public function test_it_does_not_notify_for_non_future_or_unchanged_context(): void
    {
        Notification::fake();

        $pastContext = $this->createPlanningContext(now('America/Santiago')->subDay());
        app(CoursePlanningNotifier::class)->notifyScheduled($pastContext['planificacion']);

        Notification::assertNothingSent();

        $futureContext = $this->createPlanningContext(now('America/Santiago')->addDays(7));
        app(CoursePlanningNotifier::class)->notifyUpdated($futureContext['planificacion']);
        app(CoursePlanningNotifier::class)->notifyUpdated($futureContext['planificacion']);

        Notification::assertSentTo(
            $futureContext['eligibleWorker'],
            CoursePlanningNotification::class,
            fn (CoursePlanningNotification $notification): bool => $notification->type === 'updated'
        );
        $this->assertSame(2, NotificationDelivery::where('type', NotificationDelivery::CoursePlanningUpdated)->count());
    }

    /**
     * @return array{planificacion: PlanificacionCurso, eligibleWorker: User, ineligibleWorker: User}
     */
    private function createPlanningContext(Carbon $startDate): array
    {
        Role::firstOrCreate(['name' => 'Trabajador']);

        $sede = Sede::firstOrCreate(['nombre' => 'Sede Central']);
        $otraSede = Sede::firstOrCreate(['nombre' => 'Sede Norte']);
        $estamento = Estamento::firstOrCreate(['nombre' => 'Operaciones']);
        $otroEstamento = Estamento::firstOrCreate(['nombre' => 'Administración']);

        $eligibleWorker = User::factory()->create([
            'activo' => true,
            'estamento_id' => $estamento->id,
            'sede_id' => $sede->id,
        ]);
        $eligibleWorker->assignRole('Trabajador');

        $ineligibleWorker = User::factory()->create([
            'activo' => true,
            'estamento_id' => $otroEstamento->id,
            'sede_id' => $otraSede->id,
        ]);
        $ineligibleWorker->assignRole('Trabajador');

        $curso = Curso::factory()->create();
        $curso->estamentos()->attach($estamento);

        $planificacion = PlanificacionCurso::create([
            'curso_id' => $curso->id,
            'sede_id' => $sede->id,
            'fecha_inicio' => $startDate->copy()->toDateString(),
            'fecha_fin' => $startDate->copy()->addDays(4)->toDateString(),
        ]);

        return [
            'planificacion' => $planificacion,
            'eligibleWorker' => $eligibleWorker,
            'ineligibleWorker' => $ineligibleWorker,
        ];
    }
}
