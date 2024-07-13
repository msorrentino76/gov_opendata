<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalEntity extends Model
{
    use HasFactory, SoftDeletes;
    
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
    
}
