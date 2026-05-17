<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
{
    // 1. Valider AVANT tout
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // 2. Tentative login
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Identifiants invalides'
        ], 401);
    }

    // 3. Récupérer user connecté
    $user = Auth::user();

    // sécurité supplémentaire
    if (!$user) {
        return response()->json([
            'message' => 'Utilisateur introuvable'
        ], 401);
    }

    // 4. Créer token Sanctum
    $token = $user->createToken('api-token', [$user->role])->plainTextToken;

    return response()->json([
        'user' => $user->load('agency', 'unit'),
        'token' => $token
    ]);
}

    // 🚪 LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    // 👤 USER CONNECTÉ
    public function me(Request $request)
    {
        return response()->json(
            $request->user()->load('agency', 'unit')
        );
    }
}