<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Dcblogdev\MsGraph\Facades\MsGraph;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar la vista de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Manejar la autenticación normal (email/contraseña).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Conectar con Microsoft y autenticar con Azure.
     */
    public function connect()
    {
        return MsGraph::connect();
    }

    /**
     * Cerrar sesión de Microsoft.
     */
    public function logout()
    {
        return MsGraph::disconnect();
    }

    public function callback()
    {
        try {
            $token = MsGraph::connect();
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['error' => 'No se pudo autenticar con Microsoft.']);
        }
    }
}
