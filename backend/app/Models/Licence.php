<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Panoscape\History\HasHistories;

use Carbon\Carbon;

use App\Models\User;
use App\Models\LegalEntity;

class Licence extends Model
{
    use HasFactory, SoftDeletes, HasHistories;
    
    protected $hidden = [];

    protected $fillable = [
        'user_id',
        'legal_entity_id',
        'valida_da',
        'valida_a',
    ];
    
    protected $appends = ['user', 'legal', 'expired_days']; 


    public function getUserAttribute() {
        return (string)User::find($this->user_id);
    }
    
    public function getLegalAttribute() {
        return (string)LegalEntity::find($this->legal_entity_id);
    }
    
    public function getExpiredDaysAttribute() {
        $date  = Carbon::createFromFormat('Y-m-d H:i:s', $this->getRawOriginal('valida_a'));
        $today = Carbon::now();
        return $today->diffInDays($date);
    }
    
    public function getValidaDaAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function getValidaAAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
    
    public function getUpdatedAtAttribute($value) {
        return Carbon::parse($value)->locale('it')->isoFormat('D/MM/YYYY'); //isoFormat('dddd D MMMM YYYY');
    }
 
    public function getModelLabel(){
        return $this->display_name;
    }
    
}
