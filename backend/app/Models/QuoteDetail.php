<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteDetail extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'quote_details';
    
    protected $fillable = [
        'quote_id',
        'user_id',
        'percentuale_vendita' ,
        'dividendo_vendita'   ,
        'percentuale_attivita',
        'dividendo_attivita'  ,
    ];
    
    protected $hidden = [
        'quote_id'  ,
        'user_id'   ,
        'deleted_at',        
        'created_at',
        'updated_at',
    ];
    
    public function quote(){
        return $this->belongsTo(Quote::class);
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
