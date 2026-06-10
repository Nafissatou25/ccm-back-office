<?php

namespace App\Services;

use App\Models\User;

class LoginRedirectService
{
    public static function redirect(User $user): string
{
    return match (strtoupper($user->role?->name ?? '')) {

        'ADMIN' => route('admin.dashboard'),
        'TECHNICIAN' => route('tickets.index'),

        default => route('dashboard'),
    };
}
}