<?php

namespace App\Livewire\Developer\SaludLms;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ErrorLogsPanel extends Component
{
    public string $level = 'all';

    public string $search = '';

    public ?string $status = null;

    public ?string $pendingAction = null;

    public ?string $pendingFile = null;

    public function requestDeleteFile(string $fileName): void
    {
        Gate::authorize('viewLmsHealth');

        $this->pendingAction = 'delete_file';
        $this->pendingFile = $fileName;
        $this->status = 'Confirma el borrado de '.$fileName.'. Esta acción elimina el archivo completo.';
    }

    public function cancelPendingAction(): void
    {
        $this->pendingAction = null;
        $this->pendingFile = null;
        $this->status = null;
    }

    public function confirmPendingAction(LmsHealthService $healthService): void
    {
        Gate::authorize('viewLmsHealth');

        $user = auth()->user();

        abort_unless($user, 403);

        if ($this->pendingAction === 'delete_file' && $this->pendingFile) {
            $deleted = $healthService->deleteLogFile($this->pendingFile, $user);
            $this->status = $deleted ? 'Log eliminado correctamente.' : 'No se pudo eliminar el log solicitado.';
        }

        $this->pendingAction = null;
        $this->pendingFile = null;
    }

    public function render(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('livewire.developer.salud-lms.error-logs-panel', [
            'logs' => $healthService->errorLogs($this->level, $this->search),
            'logStorage' => $healthService->logStorageSummary(),
        ]);
    }
}
