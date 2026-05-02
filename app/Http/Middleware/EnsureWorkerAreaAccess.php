<?php

namespace App\Http\Middleware;

use App\Support\UserAreaRedirector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkerAreaAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No tienes permisos para acceder a esta área.');
        }

        if (UserAreaRedirector::canAccessUserArea($user)) {
            return $next($request);
        }

        return redirect()->route(UserAreaRedirector::userAreaFallbackRouteName($user));
    }
}
