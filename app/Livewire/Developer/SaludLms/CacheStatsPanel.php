<?php

namespace App\Livewire\Developer\SaludLms;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class CacheStatsPanel extends Component
{
    public function render(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('livewire.developer.salud-lms.cache-stats-panel', [
            'stats' => $healthService->cacheStats(),
        ]);
    }
}
