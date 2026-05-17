<?php

namespace App\Services;

use App\Models\TicketLog;

class TicketLogger
{
    public static function log($ticket, $action, $description = null)
    {
        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}