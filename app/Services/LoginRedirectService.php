<?php

namespace App\Services;

use App\Models\User;

class LoginRedirectService
{
    public static function redirect(User $user): string
{
    $role = strtolower($user->role?->name ?? '');

    return match ($user->role?->name) {

    'ADMIN' => route('admin.dashboard'),

    'MANAGER' => route('tickets.index'),
    'SUPERVISOR' => route('tickets.index'),
    'CUSTOMER_SERVICE' => route('tickets.index'),

    default => route('tickets.index'),
};
}
}