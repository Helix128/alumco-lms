<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\EditHistory;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\ReportePreset;
use App\Models\SeccionCurso;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PurgeDeletedContent extends Command
{
    protected $signature = 'lms:purge-deleted-content {--days=30 : Días de conservación antes de la purga}';

    protected $description = 'Purga definitiva de contenido eliminado tras el período de recuperación';

    public function handle(MediaAttachmentService $attachments): int
    {
        $days = max(30, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $purged = 0;

        Curso::onlyTrashed()->where('deleted_at', '<=', $cutoff)->eachById(function (Curso $course) use ($attachments, &$purged): void {
            $course->modulos()->withTrashed()->each(function (Modulo $module) use ($attachments): void {
                $attachments->detachAll($module);
            });
            $attachments->detachAll($course);
            $course->forceDelete();
            $purged++;
        });

        Modulo::onlyTrashed()->where('deleted_at', '<=', $cutoff)->eachById(function (Modulo $module) use ($attachments, &$purged): void {
            $attachments->detachAll($module);
            $module->forceDelete();
            $purged++;
        });

        foreach ([Opcion::class, Pregunta::class, Evaluacion::class, SeccionCurso::class, PlanificacionCurso::class, ReportePreset::class] as $model) {
            /** @var Builder $query */
            $query = $model::onlyTrashed()->where('deleted_at', '<=', $cutoff);
            $query->eachById(function ($record) use (&$purged): void {
                $record->forceDelete();
                $purged++;
            });
        }

        EditHistory::where('expires_at', '<=', now())->delete();
        $this->info("Registros purgados definitivamente: {$purged}.");

        return self::SUCCESS;
    }
}
