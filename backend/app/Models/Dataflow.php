<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataflow extends Model
{
    use HasFactory;
    
    protected $table = 'dataflows';
    
    protected $fillable = ['flow_ref', 'category', 'data_struct', 'is_final', 'name', 'version'];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    protected $appends = ['filter_count'];
        
    public function getFilterCountAttribute() {
        return $this->dataStructures()->count();
    }
    
    public function availableConstraints(){
        return $this->hasMany(AvailableConstraints::class);
    }
    
    public function dataStructures(){
        return $this->hasMany(DataStructure::class);
    }
    
    public function available_territory() {
        return $this->hasOne(AvailableConstraints::class)->where('key', 'ITTER107')->select(['dataflow_id', 'json_value']);
    }
}
