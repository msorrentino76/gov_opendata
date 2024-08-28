<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Panoscape\History\HasHistories;

use App\Models\OuUser;
use App\Models\User;

use App\Models\StoricoOuUser;

class OrganizativeUnit extends Model
{
    use HasFactory, SoftDeletes, HasHistories;
    
    protected $table = 'organizative_units';
    
    protected $hidden = [
        'legal_entity_id',
        'cod_amm',
        'cod_uni_ou',
        'cod_aoo',
        'deleted_at',        
        'created_at',
        'updated_at',
    ];
    
    protected $fillable = [
        
        'legal_entity_id',
        
        'cod_amm',
        'cod_uni_ou',
        'cod_aoo',
        'des_ou',   // <-
        'regione',
        'provincia',
        'comune',
        'cap',      // <-
        'indirizzo',// <-
        'tel',      // <-
        'fax',      // <-
        'mail1',    // <-
        'mail2',    // <-
        'mail3',    // <-
        'nome_resp',// <-
        'cogn_resp',// <-
        'mail_resp',// <-
        'tel_resp', // <-
    ];
    
    public function ouUsers(){
        return $this->hasMany(OuUser::class);    
    }
    
    public function users(){
        $users = [];
        foreach ($this->ouUsers as $ou){
            $users[] = User::find($ou->user_id);
        }
        return $users;
    }
    
    public function userids(){
        $users = [];
        foreach ($this->ouUsers as $ou){
            $users[] = $ou->user_id;
        }
        return $users;
    }
    
    public function storicoOuUser(){
        return $this->hasMany(StoricoOuUser::class)->orderBy('performed_at');
    }
    
    public function getModelLabel() {
        return $this->display_name;
    }
    
}
