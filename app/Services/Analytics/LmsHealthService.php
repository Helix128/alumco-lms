<?php

namespace App\Services\Analytics;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Feedback;
use App\Models\Modulo;
use App\Models\NotificationDelivery;
use App\Models\SupportTicket;
use App\Models\SystemTaskRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LmsHealthService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'jobs_fallidos' => DB::table('failed_jobs')->count(),
            'notificaciones_7d' => NotificationDelivery::where('sent_at', '>=', now()->subDays(7))->count(),
            'feedback_plataforma_nuevo' => Feedback::where('tipo', Feedback::TipoPlataforma)
                ->where('estado', Feedback::EstadoNuevo)
                ->count(),
            'tickets_abiertos' => SupportTicket::open()->count(),
            'tickets_criticos' => SupportTicket::open()
                ->where('priority', SupportTicket::PriorityCritical)
                ->count(),
            'tareas_recientes' => SystemTaskRun::query()
                ->orderByDesc('started_at')
                ->limit(8)
                ->get(),
            'alertas' => $this->alerts(),
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, level: string}>
     */
    private function alerts(): array
    {
        return [
            [
                'label' => 'Cursos sin módulos',
                'value' => Curso::doesntHave('modulos')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Cursos sin audiencia',
                'value' => Curso::doesntHave('estamentos')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Cursos sin planificación',
                'value' => Curso::doesntHave('planificaciones')->count(),
                'level' => 'warning',
            ],
            [
                'label' => 'Evaluaciones sin preguntas',
                'value' => Evaluacion::doesntHave('preguntas')->count(),
                'level' => 'danger',
            ],
            [
                'label' => 'Módulos de archivo incompletos',
                'value' => Modulo::whereIn('tipo_contenido', ['video', 'pdf', 'ppt', 'imagen'])
                    ->whereNull('ruta_archivo')
                    ->count(),
                'level' => 'danger',
            ],
            [
                'label' => 'Usuarios activos sin sede o estamento',
                'value' => User::where('activo', true)
                    ->where(fn ($query) => $query->whereNull('sede_id')->orWhereNull('estamento_id'))
                    ->count(),
                'level' => 'warning',
            ],
        ];
    }
}
