<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;

class ModificaLicenza extends Notification
{
    use Queueable;

    private $subject;
    private $licence;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($subject, $licence)
    {
        $this->subject = $subject;
        $this->licence = $licence;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                ->greeting('Gentile ' . $this->subject)
                ->line('è stata modifico il periodo di durata della Sua licenza su ' . config('app.name') . ' pertinente a:') 
                ->line(new HtmlString('<b>' . $this->licence->legal . '</b>'))
                ->line(new HtmlString('Il nuovo periodo di licenza andrà dal <b>' . $this->licence->valida_da . '</b> al <b>' . $this->licence->valida_a . '</b>'))
                ->action('Accedi', config('app.url'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
