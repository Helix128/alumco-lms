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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = auth()->user();

            return redirect()->to(UserAreaRedirector::intendedOrCanonicalUrl($request, $user));
        }

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
