<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalChannel;

class TicketStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Ticket $ticket,
        private string $message,
        private string $type
    ) {}

    public function via($notifiable)
    {
        return [OneSignalChannel::class];
    }

    public function toOneSignal($notifiable)
    {
        $title = "Ticket #{$this->ticket->id} - {$this->type}";
        return OneSignalMessage::create()
            ->setSubject($title)
            ->setBody($this->message)
            ->setData('ticket_id', (string)$this->ticket->id)
            ->setData('type', $this->type);
    }
}