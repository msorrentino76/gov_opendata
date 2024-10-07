<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataStructureErrors extends Model
{
    use HasFactory;
    
    protected $table = 'data_structure_errors';
    
    protected $fillable = ['dataflow_id', 'flow_ref', 'error_msg'];
    
}
