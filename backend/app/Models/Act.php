<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Act extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'activities';
    
    protected $fillable = [
        'user_id',
        'data',
        'descrizione',
        'ore',
    ];
    
    protected $hidden = [
        'user_id',
        'deleted_at',        
        'created_at',
        'updated_at',
    ]; 
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
