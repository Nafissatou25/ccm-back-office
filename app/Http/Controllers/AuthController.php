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
        'matricule'    => 'required|string',
        'password' => 'required'
    ]);

    // ✅ Branche API (Flutter)
    if ($request->expectsJson() || $request->is('api/*')) {
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Matricule ou mot de passe incorrect'
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]
        ]);
    }

    // ✅ Branche Web (Blade)
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->to(LoginRedirectService::redirect(Auth::user()));
    }

    return back()->withErrors([
        'matricule' => 'Matricule ou mot de passe incorrect'
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