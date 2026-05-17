<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;

class CheckSlaBreaches extends Command
{
    protected $signature = 'sla:check';

    protected $description = 'Check SLA breaches';

    public function handle()
    {
        Ticket::where('status', '!=', 'RESOLVED')
            ->where('resolution_due_at', '<', now())
            ->where('is_sla_breached', false)
            ->update([
                'is_sla_breached' => true
            ]);

        $this->info('SLA breaches checked successfully.');

        return 0;
    }
}