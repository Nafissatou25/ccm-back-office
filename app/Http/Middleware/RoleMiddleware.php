<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // conversion string → id
        $map = [
            'ADMIN' => 1,
            'MANAGER' => 2,
            'SUPERVISOR' => 3,
            'TECHNICIAN' => 4,
            'ENTERPRISE' => 5,
            'CUSTOMER_SERVICE' => 6,
        ];

        foreach ($roles as $role) {
            if (isset($map[$role]) && $user->role_id === $map[$role]) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Forbidden (insufficient role)'
        ], 403);
    }
}
