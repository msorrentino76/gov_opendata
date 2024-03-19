<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'documents';
    
    protected $fillable = [
        'user_id',
        'documentable_type',
        'documentable_id',
        'name',
        'size',
        'mime',
        'content',
        'description',
    ];
    
    public function toMorph($entity) {
        $this->documentable_type = get_class($entity);
        $this->documentable_id = $entity->id;
        $this->save();
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
