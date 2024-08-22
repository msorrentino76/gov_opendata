<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;

use Panoscape\History\HasHistories;

use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasHistories;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'abilities',
        'created_by',
        'enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'password_changed',
        'notify_email',
        'deleted_at',        
        'created_at',
        'updated_at',
        'created_by',
    ];

    protected $appends = ['last_login']; 
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'notify_email'      => 'boolean',
        'enabled'           => 'boolean',
        //'created_at'        => ,
        //'updated_at'        => ,
    ];
    
    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function getUpdatedAtAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function getLastLoginAttribute() {
        $last_login = $this->storico()->orderBy('data_ora', 'desc')->offset(1)->limit(1)->first();
        return is_null($last_login) ? '-' : Carbon::parse($last_login->data_ora)->locale('it')->isoFormat('D/MM/YYYY HH:mm'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function licence() {
        return $this->hasOne(Licence::class);
    }
    
    public function username(){
        return 'username';
    }
    
    public function documents(){
        return $this->hasMany(Document::class);
    } 
    
    public function storico(){
        return $this->hasMany(StoricoLogin::class);
    }  
    
    public function getModelLabel(){
        return $this->display_name;
    }
    
    public function __toString() {
        return $this->name . ' ' . $this->surname;
    }
    
    /*
    public function numieRole() {
        $abilities = json_decode($this->abilities);
        foreach($abilities as $ability){
            $r = explode(':', $ability);
            if($r[0] == 'numie') return $r[1];
        }
        return 'no_role';
    }
    */
    
}
