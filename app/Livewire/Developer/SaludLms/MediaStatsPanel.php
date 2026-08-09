<?php

namespace App\Livewire\Developer\SaludLms;

use App\Models\Curso;
use App\Models\MediaAsset;
use App\Models\MediaUpload;
use App\Models\Modulo;
use App\Services\Media\MediaCapacityService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;

class MediaStatsPanel extends Component
{
    public function render(MediaCapacityService $capacity): View
    {
        Gate::authorize('viewLmsHealth');
        $legacyMissing = Curso::whereNotNull('imagen_portada')->get()
            ->filter(fn (Curso $curso) => ! $curso->mediaAttachments()->where('active', true)->exists() && ! Storage::disk('public')->exists($curso->imagen_portada))->count()
            + Modulo::whereNotNull('ruta_archivo')->get()
                ->filter(fn (Modulo $modulo) => ! filter_var($modulo->ruta_archivo, FILTER_VALIDATE_URL) && ! $modulo->mediaAttachments()->where('active', true)->exists() && ! Storage::disk('public')->exists($modulo->ruta_archivo))->count();

        return view('livewire.developer.salud-lms.media-stats-panel', [
            'capacity' => $capacity->status(),
            'failed' => MediaAsset::where('status', 'failed')->count(),
            'stuck' => MediaAsset::where('status', 'processing')->where('updated_at', '<', now()->subHours(2))->count(),
            'unreferenced' => MediaAsset::doesntHave('attachments')->count(),
            'expiredUploads' => MediaUpload::where('expires_at', '<', now())->whereNotIn('status', ['completed', 'cancelled', 'expired'])->count(),
            'legacyMissing' => $legacyMissing,
        ]);
    }
}
