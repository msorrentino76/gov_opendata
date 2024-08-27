<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\OrganizativeUnit;

use Panoscape\History\HasHistories;

class LegalEntity extends Model
{
    use HasFactory, SoftDeletes, HasHistories;
    
    protected $table = 'legal_entities';
    
    protected $hidden = [
        'deleted_at',        
        'created_at',
        'updated_at',
    ];
    
    protected $fillable = [
        'cod_amm',
        'acronimo',
        'des_amm',
        'regione',
        'provincia',
        'comune',
        'cap',
        'indirizzo',
        'titolo_resp',
        'nome_resp',
        'cogn_resp',
        'sito_istituzionale',
        'liv_access',
        'mail1',
        'mail2',
        'mail3',
        'mail4',
        'mail5',
        'tipologia',
        'categoria',
        'data_accreditamento',
        'cf',
        'telefono',
        'fax',    
    ];
 
    protected $appends = ['full_address', 'titolare'];
    
    public function licence() {
        return $this->hasOne(Licence::class);
    }
    
    public function organizativeUnits(){
        return $this->hasMany(OrganizativeUnit::class);
    }
    
    public function getFullAddressAttribute() {
         return ($this->indirizzo ?? '') . ' ' . ($this->cap ?? '') . ' ' . ($this->provincia ?? '') . ' ' . ($this->regione ?? '');
    }
    
    public function getTitolareAttribute() {
        return ($this->titolo_resp ?? '') . ' ' . ($this->cogn_resp ?? '') . ' ' . ($this->nome_resp ?? '');
    }
    
    public function __toString() {
        return $this->des_amm . ' (' . $this->cf . ')';
    }
    
    public function getModelLabel(){
        return $this->display_name;
    }
    
}
