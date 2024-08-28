<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAddedRemovedFromOu extends Notification
{
    use Queueable;

    private $messaggio;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($mittente, $action, $des_ou)
    {
        $this->messaggio = $mittente . ($action == 'add' ? ' ti ha aggiungo all\' ' : ' ti ha rimosso dall\' ') . 'Unità Organizzativa ' . $des_ou;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable) {
        return [
            'message' => $this->messaggio,
        ];
    }
    
}
