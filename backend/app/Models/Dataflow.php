<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataflow extends Model
{
    use HasFactory;
    
    protected $table = 'dataflows';
    
    protected $fillable = ['flow_ref', 'category', 'data_struct', 'is_final', 'name', 'version'];
    
}
