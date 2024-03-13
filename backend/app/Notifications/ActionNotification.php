<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ActionNotification extends Notification
{
    
    const ATTIVITA = 'ACT';
    const VENDITA  = 'SELL';
    
    
    /* $user_destinatario->notify(
     *      new ActionNotification(
     *          $user_che_ha_fatto_action,
     *          $user_destinatario,
     *          ActionNotification::ATTIVITA,
     *          $entita_appena_inserita->id,
     *      )   
     * );
     */

    private $destinatario;
    private $messaggio;
    private $id_entity;
    
    public function __construct($mittente, $destinatario, $azione, $id_entity)
    {
        
        $messaggio = '';
        
        switch ($azione) {
            
            case self::ATTIVITA :
                $messaggio = 'ha inserito l\'attività';
                break;

            case self::VENDITA :
                $messaggio = 'ha inserito la vendita';
                break;
            
        }
        
        $this->destinatario = $destinatario;
        $this->messaggio = $mittente->name . ' ' . $mittente->surname . ' ' . $messaggio . ' #' . $id_entity;
        $this->id_entity = $id_entity;
        
    }

    public function via($notifiable) {
        return ($this->destinatario->notify_email === true) ? ['mail', 'database'] : ['database'];
    }
    
    public function toDatabase($notifiable) {
        return [
            'id' => $this->id_entity,
            'message' => $this->messaggio,
        ];
    }
    
    public function toMail($notifiable){
        return (new MailMessage)
                    ->subject('Centro notifiche Numieport')
                    ->greeting('Salve ' . $this->destinatario->name . ' ' . $this->destinatario->surname)
                    ->line($this->messaggio)
                    //->lineIf($this->amount > 0, "Amount paid: {$this->amount}")
                    ->action('Accedi a sistema', config('app.url'));
    }

}
