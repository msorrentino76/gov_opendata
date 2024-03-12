<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ActionNotification extends Notification
{

    public $mittente;
    public $azione;
    public $id_entity;

    const ATTIVITA = 'ACT';
    const VENDITA  = 'SELL';
    
    
    //$user_destinatario->notify(new ActionNotification($user_che_ha_fatto_l'action, ActionNotification::ATTIVITA, $entita_appena_inserita->id));
    
    public function __construct($mittente, $azione, $id_entity)
    {
        $this->mittente  = $mittente;
        $this->azione    = $azione;
        $this->id_entity = $id_entity;
    }

    public function via($notifiable)
    {
        return ['database'];
    }
    
    public function toDatabase($notifiable)
    {
        $message = '';
        
        switch ($this->azione) {
            
            case self::ATTIVITA :
                $message = 'ha inserito l\'attività';
                break;

            case self::VENDITA :
                $message = 'ha inserito la vendita';
                break;
            
        }
        
        return [
            'id' => $this->id_entity,
            'message' => $this->mittente->name . ' ' . $this->mittente->surname . ' ' . $message . ' ' . $this->id_entity,
        ];
    }
}
