<?php

namespace App\Livewire\Developer\SaludLms;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class QuickActionsPanel extends Component
{
    public ?string $message = null;

    public ?string $pendingAction = null;

    public function requestAction(string $action): void
    {
        Gate::authorize('viewLmsHealth');

        if (! in_array($action, ['clear_cache', 'flush_failed_jobs'], true)) {
            return;
        }

        $this->pendingAction = $action;
    }

    public function closeConfirmation(): void
    {
        $this->pendingAction = null;
    }

    public function confirmPendingAction(): void
    {
        Gate::authorize('viewLmsHealth');

        if ($this->pendingAction === 'clear_cache') {
            $this->clearCache();
        }

        if ($this->pendingAction === 'flush_failed_jobs') {
            $this->flushFailedJobs();
        }

        $this->closeConfirmation();
    }

    public function clearCache(): void
    {
        Gate::authorize('viewLmsHealth');

        app(LmsHealthService::class)->clearOptimizedCache(Auth::user());
        $this->message = 'Caché de framework limpiada y auditada.';
    }

    public function flushFailedJobs(): void
    {
        Gate::authorize('viewLmsHealth');

        app(LmsHealthService::class)->flushFailedJobs(Auth::user());
        $this->message = 'Jobs fallidos eliminados y acción auditada.';
    }

    public function render(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('livewire.developer.salud-lms.quick-actions-panel', [
            'actions' => $healthService->recentAdminActions(),
            'pendingConfirmation' => $this->pendingConfirmation(),
        ]);
    }

    /**
     * @return array{show: bool, title: string, description: string, confirm_label: string, tone: string}
     */
    private function pendingConfirmation(): array
    {
        return match ($this->pendingAction) {
            'clear_cache' => [
                'show' => true,
                'title' => 'Limpiar caché optimizada',
                'description' => 'Se ejecutará optimize:clear y se registrará la acción en auditoría.',
                'confirm_label' => 'Limpiar caché',
                'tone' => 'primary',
            ],
            'flush_failed_jobs' => [
                'show' => true,
                'title' => 'Vaciar jobs fallidos',
                'description' => 'Se eliminarán todos los jobs fallidos registrados. La acción quedará auditada.',
                'confirm_label' => 'Vaciar jobs fallidos',
                'tone' => 'danger',
            ],
            default => [
                'show' => false,
                'title' => '',
                'description' => '',
                'confirm_label' => 'Confirmar',
                'tone' => 'primary',
            ],
        };
    }
}
