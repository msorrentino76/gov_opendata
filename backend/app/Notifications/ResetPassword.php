<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\HtmlString;

class ResetPassword extends Notification
{
    use Queueable;

    private $subject;
    private $username;
    private $password;
    
    /**
     * Create a new notification instance.
     */
    public function __construct($subject, $username, $password)
    {
        $this->subject  = $subject;
        $this->username = $username;
        $this->password = $password;
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
                ->line('la Sua password di accesso al sistema ' . config('app.name') . ' è stata resettata dall\'Amministratore.') 
                ->line(new HtmlString('La nuova password: <b>' . $this->password . '</b>'))
                ->line(new HtmlString('<u>Si ricordi di cambiarla al prossimo accesso dal tuo Profilo.</u>'))
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
