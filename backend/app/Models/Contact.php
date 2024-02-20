 <?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Group;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'contacts';
    
    protected $fillable = [
        'organization',
        'name',
        'surname',
        'emails',
        'phones',
        'pecs',
        'available',
    ];

    public function groups(){
        return $this->belongsToMany(Group::class);
    }
    
}
