<?php

namespace App\Services;

use App\Models\Ticket;

class TicketActionService
{
    public static function allowedActions(Ticket $ticket): array
    {
        return match ($ticket->status) {

            // Ticket ouvert
            'OPEN', 'REOPENED', 'REJECTED' => [
                'assign',
                'transfer',
                'start',
                'comment',
                'document',
            ],

            // Ticket assigné ou transféré
            'ASSIGNED', 'TRANSFERRED', 'ASSIGNED_TO_TECHNICIANS' => [
                'transfer',
                'start',
                'comment',
                'document',
            ],

            // Ticket en cours
            'IN_PROGRESS' => [
                'comment',
                'document',
                'resolve',
                'transfer',
            ],

            // Ticket résolu
            'RESOLVED' => [
                'close',
                'reopen',
                'comment',
                'document',
            ],

            // Ticket clôturé
            'CLOSED' => [
                'reopen',
            ],

            default => [],
        };
    }

    public static function can(Ticket $ticket, string $action): bool
    {
        return in_array($action, self::allowedActions($ticket));
    }
}