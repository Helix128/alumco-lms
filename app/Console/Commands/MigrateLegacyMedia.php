<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Modulo;
use App\Services\Media\MediaAssetService;
use App\Services\Media\MediaAttachmentService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MigrateLegacyMedia extends Command
{
    protected $signature = 'media:migrate-legacy {--dry-run : Solo informa, sin modificar datos}';

    protected $description = 'Importa portadas y archivos legados al almacenamiento multimedia inmutable';

    private array $referenced = [];

    private int $imported = 0;

    private int $missing = 0;

    private int $skipped = 0;

    public function handle(MediaAssetService $assets, MediaAttachmentService $attachments): int
    {
        Curso::with('capacitador')->whereNotNull('imagen_portada')->eachById(function (Curso $curso) use ($assets, $attachments): void {
            $this->migrate($curso, 'cover', $curso->imagen_portada, 'cover', $curso->capacitador, $assets, $attachments);
        });
        Modulo::with('curso.capacitador')->whereNotNull('ruta_archivo')->eachById(function (Modulo $modulo) use ($assets, $attachments): void {
            if (filter_var($modulo->ruta_archivo, FILTER_VALIDATE_URL)) {
                $this->line("EXTERNO módulo {$modulo->id}: {$modulo->ruta_archivo}");
                $this->skipped++;

                return;
            }
            $purpose = match ($modulo->tipo_contenido) {
                'video' => 'video', 'imagen' => 'image', 'pdf' => 'pdf', 'ppt', 'documento' => 'document', default => null,
            };
            if ($purpose) {
                $this->migrate($modulo, 'content', $modulo->ruta_archivo, $purpose, $modulo->curso->capacitador, $assets, $attachments);
            }
        });

        $orphans = collect(Storage::disk('public')->allFiles())
            ->reject(fn (string $path) => in_array($path, $this->referenced, true));
        foreach ($orphans as $path) {
            $this->warn("HUÉRFANO (no eliminado): {$path}");
        }

        $this->newLine();
        $this->info("Importados: {$this->imported}; omitidos: {$this->skipped}; faltantes: {$this->missing}; huérfanos: {$orphans->count()}.");

        return $this->missing > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function migrate(Model $target, string $collection, string $path, string $purpose, $owner, MediaAssetService $assets, MediaAttachmentService $attachments): void
    {
        $this->referenced[] = $path;
        if ($target->mediaAttachments()->where('collection', $collection)->exists()) {
            $this->skipped++;

            return;
        }
        if (! Storage::disk('public')->exists($path)) {
            $this->error('FALTANTE '.get_class($target)."#{$target->getKey()}: {$path}");
            $this->missing++;

            return;
        }
        $this->line(($this->option('dry-run') ? 'IMPORTARÍA' : 'IMPORTA').' '.get_class($target)."#{$target->getKey()}: {$path}");
        if ($this->option('dry-run')) {
            return;
        }
        try {
            $asset = $assets->ingestPath(Storage::disk('public')->path($path), basename($path), $purpose, $owner);
            $attachments->request($asset, $target, $collection, $owner);
            $this->imported++;
        } catch (\Throwable $exception) {
            $this->error("MIME/ESTRUCTURA INVÁLIDA {$path}: {$exception->getMessage()}");
            $this->missing++;
        }
    }
}
