<?php

namespace App\Http\Controllers;

use App\Services\Analytics\LmsHealthService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DevHealthController extends Controller
{
    public function __invoke(LmsHealthService $healthService): View
    {
        Gate::authorize('viewLmsHealth');

        return view('dev.salud-lms', [
            'health' => $healthService->snapshot(),
        ]);
    }
}
