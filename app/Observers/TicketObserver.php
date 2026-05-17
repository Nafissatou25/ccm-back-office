<?php

namespace App\Observers;

use App\Models\SlaRule;

class TicketObserver
{
    public function created($ticket)
    {
        if (!$ticket->priority || !$ticket->unit_id) {
            return;
        }

        $sla = SlaRule::where('unit_id', $ticket->unit_id)
            ->where('priority', $ticket->priority)
            ->where('is_active', 1)
            ->first();

        if (!$sla) {
            return;
        }

        $ticket->updateQuietly([
            'sla_id' => $sla->id,

            'response_due_at' => now()->addMinutes($sla->response_time),
            'resolution_due_at' => now()->addMinutes($sla->resolution_time),
        ]);
    }
}