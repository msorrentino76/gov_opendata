<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataStructure extends Model
{
    use HasFactory;
        
    protected $table = 'data_structures';
    
    protected $fillable = ['dataflow_id', 'flow_ref', 'data_struct', 'position', 'codelist'];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function dataflow(){
        return $this->belongsTo(Dataflow::class, 'dataflow_id');
    }
    
}
