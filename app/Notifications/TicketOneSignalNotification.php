<?php

namespace App\Notifications;

use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use Illuminate\Notifications\Notification;

class TicketOneSignalNotification extends Notification
{
    public function via($notifiable)
    {
        return [OneSignalChannel::class];
    }

    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->setSubject("Ticket mis à jour")
            ->setBody("Le ticket #{$notifiable->ticket_id} a été modifié.")
            ->setData(['ticket_id' => $notifiable->ticket_id]);
    }
    
}