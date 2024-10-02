<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codelist extends Model
{
    use HasFactory;
    
    protected $table = 'codelists';
    
    protected $fillable = ['codelist', 'urn', 'version','name'];
    
    public function codes(){
        return $this->hasMany(Code::class);
    }
    
    public function __toString(): string {
        return $this->name;
    }
    
}
