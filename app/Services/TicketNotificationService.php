<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class TicketNotificationService
{
    public function __construct(private FcmService $fcm) {}

    public function notifyAssigned(Ticket $ticket): void
    {
        $ticket->loadMissing('technicians');
        $tokens = $this->tokens($ticket->technicians);
        \Log::info("NOTIFY ASSIGNED - Technicians:", $ticket->technicians->pluck('id')->toArray());
        \Log::info("NOTIFY ASSIGNED - Tokens:", $tokens);
        if (empty($tokens)) return;

        $this->fcm->sendToTokens($tokens,
            '🔧 Nouveau ticket assigné',
            "Ticket #{$ticket->id} — {$this->unit($ticket)} vous a été assigné",
            ['ticket_id' => (string)$ticket->id, 'type' => 'assigned']
        );
    }

    public function notifyResolved(Ticket $ticket): void
    {
        $users  = $this->supervisorsAndCustomerService($ticket);
        $tokens = $this->tokens($users);
        if (empty($tokens)) return;

        $this->fcm->sendToTokens($tokens,
            '✅ Ticket résolu',
            "Ticket #{$ticket->id} — {$this->unit($ticket)} a été résolu",
            ['ticket_id' => (string)$ticket->id, 'type' => 'resolved']
        );
    }

    public function notifyClosed(Ticket $ticket): void
    {
        $ticket->loadMissing('technicians');
        $tokens = $this->tokens($ticket->technicians);
        if (empty($tokens)) return;

        $this->fcm->sendToTokens($tokens,
            '🔒 Ticket clôturé',
            "Ticket #{$ticket->id} — {$this->unit($ticket)} a été clôturé",
            ['ticket_id' => (string)$ticket->id, 'type' => 'closed']
        );
    }

    public function notifyReopened(Ticket $ticket): void
    {
        $ticket->loadMissing('assignedTo');
        $token = $ticket->assignedTo?->fcm_token;
        if (!$token) return;

        $this->fcm->sendToToken($token,
            '🔄 Ticket réouvert',
            "Ticket #{$ticket->id} — {$this->unit($ticket)} a été réouvert",
            ['ticket_id' => (string)$ticket->id, 'type' => 'reopened']
        );
    }

    public function notifySlaBreached(Ticket $ticket): void
    {
        $users = collect();
        $ticket->loadMissing('assignedTo');
        if ($ticket->assignedTo) $users->push($ticket->assignedTo);

        $managers = User::whereHas('role', fn($q) =>
            $q->whereIn('name', ['MANAGER', 'ADMIN'])
        )->get();

        $tokens = $this->tokens($users->merge($managers)->unique('id'));
        if (empty($tokens)) return;

        $this->fcm->sendToTokens($tokens,
            '⚠️ SLA dépassé',
            "Ticket #{$ticket->id} — {$this->unit($ticket)} a dépassé son délai SLA",
            ['ticket_id' => (string)$ticket->id, 'type' => 'sla_breached']
        );
    }

    // ── Helpers ───────────────────────────────────────────────

    private function unit(Ticket $ticket): string
    {
        $ticket->loadMissing('unit');
        return $ticket->unit?->name ?? "Ticket #{$ticket->id}";
    }

    private function tokens($users): array
    {
        return collect($users)->pluck('fcm_token')->filter()->values()->toArray();
    }

    private function supervisorsAndCustomerService(Ticket $ticket): \Illuminate\Support\Collection
    {
        $ticket->loadMissing('assignedTo');
        $users = collect();
        if ($ticket->assignedTo) $users->push($ticket->assignedTo);

        return $users->merge(
            User::whereHas('role', fn($q) =>
                $q->where('name', 'CUSTOMER_SERVICE')
            )->get()
        )->unique('id');
    }
}