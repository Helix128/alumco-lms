<?php

namespace App\Http\Controllers;

use App\Support\UserAreaRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Maneja el intento de inicio de sesión.
     */
    public function login(Request $request)
    {
        // Validar las credenciales proporcionadas
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Intentar iniciar sesión
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Failsafe de permisos para cuentas principales (por si no se corrió la migración o falló el seeder)
            if ($user->email === 'dev@alumco.cl' && ! $user->hasRole('Desarrollador')) {
                $user->assignRole('Desarrollador');
            } elseif ($user->email === 'admin@alumco.cl' && ! $user->hasRole('Administrador')) {
                $user->assignRole('Administrador');
            }

            return redirect()->to(UserAreaRedirector::intendedOrCanonicalUrl($request, $user));
        }

        // Si falla, volver atrás con un error
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con los registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
