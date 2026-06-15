<?php

namespace App\Services;

use App\Models\Ticket;

class TicketActionService
{
    public static function allowedActions(Ticket $ticket, $user): array
{
    $role = strtoupper($user->role?->name);

    $actions = match ($ticket->status) {

        'OPEN', 'REOPENED', 'REJECTED', 'ASSIGNED', 'TRANSFERRED', 'ASSIGNED_TO_TECHNICIANS' => [
            'start',
            'assign',
            'transfer',
            'comment',
        ],

        'IN_PROGRESS' => [
            'document',
            'resolve',
            'hold',
            'comment',
        ],

        'ON_HOLD' => [
            'resume',
            'transfer',
            'comment',
        ],

        'RESOLVED' => [
            'close',
            'reopen',
            'comment',
        ],

        'CLOSED' => [
            'reopen',
        ],

        default => [],
    };

    // 🔥 TECHNICIAN restrictions
    if ($role === 'TECHNICIAN') {
        $actions = array_diff($actions, [
            'transfer',
            'assign',
            'close',
            'reopen',
        ]);
    }

    // 🔥 SUPERVISOR restrictions
    if ($role === 'SUPERVISOR') {
        $actions = array_diff($actions, [
            'close',
            'reopen',
        ]);
    }

    // 🔥 CUSTOMER SERVICE FULL LOCKDOWN
    if ($role === 'CUSTOMER_SERVICE') {
        $actions = array_diff($actions, [
            'assign',
            'hold',
            'resume'
        ]);
    }

    return array_values($actions);
}
}