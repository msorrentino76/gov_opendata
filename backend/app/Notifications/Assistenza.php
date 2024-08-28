<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;

class Assistenza extends Notification
{
    use Queueable;

    private $oggetto;
    private $testo;
    private $segnalatore;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($oggetto, $testo, $segnalatore)
    {
        $this->oggetto     = $oggetto;
        $this->testo       = $testo;
        $this->segnalatore = $segnalatore;
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
                ->subject('Assistenza ' . config('app.name') . ': ' . $this->oggetto)
                ->greeting('Nuova richiesta di assistenza ')
                ->line(new HtmlString('</b>Segnalatore:<b> ' . (string)$this->segnalatore   ))
                ->line(new HtmlString('</b>Username:<b> '    . $this->segnalatore->username ))
                ->line(new HtmlString('</b>Email:<b> '       . $this->segnalatore->email    ))
                ->line(new HtmlString('<b>Testo della richiesta:</b>'))
                ->line(new HtmlString(nl2br($this->testo)));
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
