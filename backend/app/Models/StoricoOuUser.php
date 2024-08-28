<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoricoOuUser extends Model
{
    use HasFactory;
    
    protected $table = 'storico_ou_user';
    
    public    $timestamps = false;
    
    protected $fillable = ['user_id', 'organizative_unit_id', 'action', 'users', 'performed_at'];
	    
}
