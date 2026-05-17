<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LoginRedirectService;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Afficher la page login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Connexion
     */
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        $user = Auth::user();
        return redirect()->to(LoginRedirectService::redirect($user));
    }

    return back()->withErrors([
        'email' => 'Email ou mot de passe incorrect'
    ]);
}

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}