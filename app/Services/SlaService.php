<?php

namespace App\Services;

use App\Models\SlaRule;

class SlaService
{
    public static function applySla($ticket)
    {
        $rule = SlaRule::where('unit_id', $ticket->unit_id)
            ->where('priority', $ticket->priority)
            ->where('is_active', true)
            ->first();

        if (!$rule) {
            return;
        }

        $ticket->response_due_at =
            now()->addMinutes($rule->response_time);

        $ticket->resolution_due_at =
            now()->addMinutes($rule->resolution_time);
    }
}