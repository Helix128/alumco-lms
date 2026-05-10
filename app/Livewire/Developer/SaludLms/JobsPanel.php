<?php

namespace App\Livewire\Developer\SaludLms;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class JobsPanel extends Component
{
    public string $search = '';

    public int $hours = 24;

    public ?string $message = null;

    public ?string $pendingAction = null;

    public ?string $pendingUuid = null;

    public function requestRetry(string $uuid): void
    {
        Gate::authorize('viewLmsHealth');

        $this->pendingAction = 'retry';
        $this->pendingUuid = $uuid;
    }

    public function requestForget(string $uuid): void
    {
        Gate::authorize('viewLmsHealth');

        $this->pendingAction = 'forget';
        $this->pendingUuid = $uuid;
    }

    public function closeConfirmation(): void
    {
        $this->pendingAction = null;
        $this->pendingUuid = null;
    }

    public function confirmPendingAction(): void
    {
        Gate::authorize('viewLmsHealth');

        if ($this->pendingAction === 'retry' && $this->pendingUuid) {
            $this->retry($this->pendingUuid);
        }

        if ($this->pendingAction === 'forget' && $this->pendingUuid) {
            $this->forget($this->pendingUuid);
        }

        $this->closeConfirmation();
    }

    public function retry(string $uuid): void
    {
        Gate::authorize('viewLmsHealth');

        app(LmsHealthService::class)->retryFailedJob($uuid, Auth::user());
        $this->message = 'Job reenviado a la cola.';
    }

    public function forget(string $uuid): void
    {
        Gate::authorize('viewLmsHealth');

        app(LmsHealthService::class)->forgetFailedJob($uuid, Auth::user());
        $this->message = 'Job fallido eliminado.';
    }

    public function render(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('livewire.developer.salud-lms.jobs-panel', [
            'jobs' => $healthService->failedJobs($this->search, $this->hours),
            'pendingConfirmation' => $this->pendingConfirmation(),
        ]);
    }

    /**
     * @return array{show: bool, title: string, description: string, confirm_label: string, tone: string}
     */
    private function pendingConfirmation(): array
    {
        return match ($this->pendingAction) {
            'retry' => [
                'show' => true,
                'title' => 'Reintentar job fallido',
                'description' => 'Se reenviará el job seleccionado a la cola. La acción quedará auditada.',
                'confirm_label' => 'Reintentar job',
                'tone' => 'primary',
            ],
            'forget' => [
                'show' => true,
                'title' => 'Olvidar job fallido',
                'description' => 'Se eliminará el registro del job fallido. La acción quedará auditada.',
                'confirm_label' => 'Olvidar job',
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
