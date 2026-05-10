<?php

namespace App\Livewire\Developer\SaludLms;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ConfigAlertsPanel extends Component
{
    public function render(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('livewire.developer.salud-lms.config-alerts-panel', [
            'alerts' => $healthService->configurationAlerts(),
        ]);
    }
}
