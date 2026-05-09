<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCapacitadorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || (! $request->user()->isCapacitador() && ! $request->user()->hasAdminAccess())) {
            abort(403, 'Esta sección requiere perfil de capacitador o administración.');
        }

        return $next($request);
    }
}
