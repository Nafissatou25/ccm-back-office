<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\FcmService;
use App\Services\TicketNotificationService;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Messaging;

class CheckSlaBreaches extends Command
{
    protected $signature   = 'sla:check';
    protected $description = 'Vérifie les tickets en retard SLA et notifie';

    public function handle(Messaging $messaging): void
    {
        $service = new TicketNotificationService(new FcmService($messaging));

        $breached = Ticket::whereNotIn('status', ['RESOLVED', 'CLOSED'])
            ->whereNotNull('resolution_due_at')
            ->where('resolution_due_at', '<', now())
            ->where('is_sla_breached', false)
            ->with(['assignedTo', 'unit'])
            ->get();

        foreach ($breached as $ticket) {
            $ticket->update(['is_sla_breached' => true]);
            $service->notifySlaBreached($ticket);
            $this->info("Notified SLA breach: ticket #{$ticket->id}");
        }

        $this->info("Done. {$breached->count()} tickets processed.");
    }
}