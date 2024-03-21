<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Casts\Attribute;

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
    
    protected $hidden = [
        'documentable_type',
        'documentable_id',
        'user_id',
        'deleted_at',        
        'created_at',
        'updated_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function content(): Attribute{
        return Attribute::make(
            get: fn (/*string $value*/) => $this->toLink(/*$value*/),
        );
    }
    
    private function toLink(/*$value*/){
        return route('download', ['id' => $this->id]);
    }
    
    public function toMorph($entity) {
        $this->documentable_type = get_class($entity);
        $this->documentable_id = $entity->id;
        $this->save();
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
