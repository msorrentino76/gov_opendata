<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    use HasFactory;
    
    protected $table = 'codes';
    
    protected $fillable = ['codelist_id', 'codelist', 'code','name'];
    
    public function codelist() {
        return $this->hasOne(Codelist::class);
    }
    
    public function __toString(): string {
        return $this->name;
    }
    
}
