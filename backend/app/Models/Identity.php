<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;

class Identity extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'identities';
    
    protected $fillable = [
        'name',
        'surname',
        'pec',
        'phone'
    ];
    
    public function user(){
        return $this->hasOne(User::class);
    }
    
}
