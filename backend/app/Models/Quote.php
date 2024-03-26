<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'quotes';
    
    protected $fillable = [
        'periodo_da',
        'periodo_a' ,
        'importo_totale'          ,
        'dividendo_vendita_totale',
        'importo_residuo_cassa'   ,
    ];
    
    protected $hidden = [
        'deleted_at',        
        'created_at',
        'updated_at',
    ];
    
    public function details(){
        return $this->hasMany(QuoteDetail::class);
    }    
    
}
