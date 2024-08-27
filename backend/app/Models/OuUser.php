<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use \Panoscape\History\HasHistories;

class OuUser extends Model
{
    use HasFactory, HasHistories;
    
    protected $table = 'ous_users';
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'organizative_unit_id',
        'user_id',
        'permissions',
    ];
    
    public function getModelLabel() {
        return $this->display_name;
    }
    
}
