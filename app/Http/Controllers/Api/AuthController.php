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
        'matricule' => 'required|matricule',
        'password' => 'required'
    ]);

    // 2. Tentative login
    if (!Auth::attempt($request->only('matricule', 'password'))) {
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

    public function updateFcmToken(Request $request)
{
    $request->validate(['fcm_token' => 'required|string']);
    auth()->user()->update(['fcm_token' => $request->fcm_token]);
      \Log::info('FCM HEADERS', $request->headers->all());

    \Log::info('AUTH USER', [
        'user' => auth()->user()
    ]);

    \Log::info('DATA', $request->all());

    return response()->json([
        'user' => auth()->user(),
        'data' => $request->all()
    ]);
    // return response()->json(['message' => 'Token FCM enregistré']);

}

public function updateOneSignalPlayerId(Request $request)
{
    \Log::info('ONESIGNAL REQUEST', [
        'user' => auth()->user()?->id,
        'data' => $request->all(),
    ]);
    $request->validate(['player_id' => 'required|string']);
    $user = auth()->user();
    if (!$user) {
        return response()->json(['message' => 'Non authentifié'], 401);
    }
    $user->update(['onesignal_player_id' => $request->player_id]);
    return response()->json(['message' => 'Player ID enregistré']);
}
}