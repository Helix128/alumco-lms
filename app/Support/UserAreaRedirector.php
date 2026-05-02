<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserAreaRedirector
{
    public static function canonicalRouteName(User $user): string
    {
        if (session('preview_mode')) {
            return 'cursos.index';
        }

        if ($user->hasAdminAccess()) {
            return 'admin.reportes.index';
        }

        if ($user->isCapacitador()) {
            return 'capacitador.dashboard';
        }

        return 'cursos.index';
    }

    public static function canonicalUrl(User $user): string
    {
        return route(self::canonicalRouteName($user));
    }

    public static function intendedOrCanonicalUrl(Request $request, User $user): string
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && self::canAccessUrl($request, $user, $intended)) {
            return $intended;
        }

        return self::canonicalUrl($user);
    }

    public static function canAccessUserArea(User $user): bool
    {
        if ($user->hasAdminAccess() || $user->isCapacitador()) {
            return (bool) session('preview_mode');
        }

        return true;
    }

    public static function userAreaFallbackRouteName(User $user): string
    {
        if ($user->hasAdminAccess()) {
            return 'admin.reportes.index';
        }

        if ($user->isCapacitador()) {
            return 'capacitador.dashboard';
        }

        return 'cursos.index';
    }

    private static function canAccessUrl(Request $request, User $user, string $url): bool
    {
        $path = self::pathForUrl($request, $url);

        if ($path === null) {
            return false;
        }

        if ($path === '/') {
            return true;
        }

        if (self::isAdminAreaPath($path)) {
            return $user->hasAdminAccess();
        }

        if (self::isCapacitadorAreaPath($path)) {
            return $user->isCapacitador() || $user->hasAdminAccess();
        }

        if (self::isDevAreaPath($path)) {
            return $user->isDesarrollador();
        }

        if (self::isUserAreaPath($path)) {
            return self::canAccessUserArea($user);
        }

        return true;
    }

    private static function pathForUrl(Request $request, string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && $parts['host'] !== $request->getHost()) {
            return null;
        }

        $path = $parts['path'] ?? '/';

        return '/'.ltrim($path, '/');
    }

    private static function isAdminAreaPath(string $path): bool
    {
        return Str::is('/admin/*', $path) && $path !== '/admin/preview-mode/toggle';
    }

    private static function isCapacitadorAreaPath(string $path): bool
    {
        return $path === '/capacitador' || Str::is('/capacitador/*', $path);
    }

    private static function isDevAreaPath(string $path): bool
    {
        return $path === '/dev/configuracion';
    }

    private static function isUserAreaPath(string $path): bool
    {
        return $path === '/cursos'
            || Str::is('/cursos/*', $path)
            || $path === '/calendario'
            || $path === '/calendario-cursos'
            || $path === '/perfil'
            || $path === '/mis-certificados'
            || Str::is('/mis-certificados/*', $path)
            || $path === '/ajustes';
    }
}
