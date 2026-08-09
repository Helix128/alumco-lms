<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\MediaAsset;
use App\Models\MediaUpload;
use App\Models\Modulo;
use App\Services\Media\MediaCapacityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditMedia extends Command
{
    protected $signature = 'media:audit';

    protected $description = 'Audita recursos multimedia, archivos faltantes y capacidad';

    public function handle(MediaCapacityService $capacity): int
    {
        $missingVariants = 0;
        MediaAsset::with('variants')->eachById(function (MediaAsset $asset) use (&$missingVariants): void {
            foreach ($asset->variants as $variant) {
                if (! Storage::disk($variant->disk)->exists($variant->path)) {
                    $this->error("FALTANTE asset {$asset->id}/{$variant->type}: {$variant->disk}:{$variant->path}");
                    $missingVariants++;
                }
            }
        });
        $legacyMissing = Curso::whereNotNull('imagen_portada')->get()->filter(fn (Curso $c) => ! $c->mediaAttachments()->where('active', true)->exists() && ! Storage::disk('public')->exists($c->imagen_portada))->count()
            + Modulo::whereNotNull('ruta_archivo')->get()->filter(fn (Modulo $m) => ! filter_var($m->ruta_archivo, FILTER_VALIDATE_URL) && ! $m->mediaAttachments()->where('active', true)->exists() && ! Storage::disk('public')->exists($m->ruta_archivo))->count();
        $status = $capacity->status();

        $this->table(['Métrica', 'Valor'], [
            ['Espacio usado', $this->formatBytes($status['used']).($status['total'] ? sprintf(' (%.1f%%)', $status['percent']) : '')],
            ['Variantes faltantes', $missingVariants],
            ['Legados faltantes', $legacyMissing],
            ['Procesamientos fallidos', MediaAsset::where('status', 'failed')->count()],
            ['Procesamientos atascados (>2h)', MediaAsset::where('status', 'processing')->where('updated_at', '<', now()->subHours(2))->count()],
            ['Sesiones vencidas', MediaUpload::where('expires_at', '<', now())->count()],
            ['Recursos sin referencia', MediaAsset::doesntHave('attachments')->count()],
        ]);

        return ($missingVariants + $legacyMissing) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        return number_format($bytes / 1073741824, 2).' GB';
    }
}
