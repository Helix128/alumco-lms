<?php

namespace App\Http\Controllers;

use App\Services\Analytics\LmsHealthService;
use Illuminate\View\View;

class DevHealthController extends Controller
{
    public function __invoke(LmsHealthService $healthService): View
    {
        abort_unless(auth()->user()?->isDesarrollador(), 403);

        return view('dev.salud-lms', [
            'health' => $healthService->snapshot(),
        ]);
    }
}
