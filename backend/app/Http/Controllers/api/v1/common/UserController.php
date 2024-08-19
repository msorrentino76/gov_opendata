<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

use App\Exceptions\ResponseException;

class UserController extends Controller
{
    
    public function index(){
        return response(User::where(['created_by' => Auth::user()->id])->orderBy('surname')->get(), 200);
    }
    
    public function create(Request $request){
        
        $data = $request->only('name', 'surname', 'username', 'email');
        
        $user = null;
                
        try {
            
            $length           = 8;
            $password         = bin2hex(random_bytes($length / 2));        
            $data['password'] = bcrypt($password);
            
            // system:admin       -> CREA legal_entity:admin
            // legal_entity:admin -> CREA ou:user
            if(Auth::user()->tokenCan('system:admin')) {
                $data['abilities'] = json_encode(["legal_entity:admin"]);
            }
            
            if(Auth::user()->tokenCan('legal_entity:admin')) {
                $data['abilities'] = json_encode(["ou:user"]);
            }
            
            $data['created_by'] = Auth::user()->id;
            
            $user = User::create($data);
            
        } catch (\Exception $e) {
            
        }

        return $user;
        
    }
    
    public function read(string $id){
        return $this->getResourceIfOwner($id);
    }
    
    public function update(Request $request, string $id){
        $data = $request->only('name', 'surname', 'username', 'email');
        $user = $this->getResourceIfOwner($id);
        $user->update($data);
        return $user;
    }
    
    public function destroy(string $id) {
        $user = $this->getResourceIfOwner($id);
        $user->delete();        
        return response(['id' => $id], 200);
    }
    
    public function toggle(Request $request, string $id){
        
        $user = $this->getResourceIfOwner($id);
        
        $user->enabled = !$user->enabled;        
        $user->save();
        
        return response($user, 200);
        
    }
    
    private function getResourceIfOwner($id) {
        
        $user = User::where(
                [
                    'id'         => $id,
                    'created_by' => Auth::user()->id
                ])->first();
        
        if(is_null($user)){
            throw new ResponseException(response('Risorsa non trovata', 404));
        }
        
        return $user;
        
    } 
    
}
