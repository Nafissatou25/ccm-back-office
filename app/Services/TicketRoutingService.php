<?php

namespace App\Services;

use App\Models\Ticket;

class TicketRoutingService
{
    public function route(Ticket $ticket)
    {
        switch ($ticket->category->name) {

            case 'FACTURATION':
                $ticket->unit_id = 4;
                break;

            case 'RESEAU':
                $ticket->unit_id = 2;
                break;

            case 'PETITES_INTERVENTIONS':
                $ticket->unit_id = 1;
                break;
        }

        $ticket->save();
    }
}