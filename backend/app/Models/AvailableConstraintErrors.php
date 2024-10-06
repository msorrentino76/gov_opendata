<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableConstraintErrors extends Model
{
    use HasFactory;
    
    protected $table = 'available_constraint_errors';
    
    protected $fillable = ['dataflow_id', 'flow_ref', 'error_msg'];
    
}
