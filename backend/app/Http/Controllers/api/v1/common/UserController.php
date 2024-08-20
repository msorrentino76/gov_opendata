<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

use App\Exceptions\ResponseException;

use Carbon\Carbon;

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
            $data['enabled']    = true;
            
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
    
    public function userActivities(string $id) {
        
        $user = $this->getResourceIfOwner($id);
        $hist = $user->histories()->orderBy('performed_at', 'asc')->get();
        
        $activities = [];
        
        foreach ($hist as $h){
            $owner = (string)User::find($h->user_id);
            $timestamp = Carbon::parse($h->performed_at)->locale('it')->isoFormat('D/MM/YYYY HH:mm');
            if($h->message == 'CREATE') {
                $activities[] = ['content' => 'Creato da ' . $owner, 'timestamp' => $timestamp, 'type' => 'success'];
            }
            if($h->message == 'UPDATE') {
                
                $data_update = false;
                
                foreach($h->meta as $row){                    
                    if($row['key'] == 'enabled'){
                        $activities[] = ['content' => ($row['new'] ? 'Riabilitato da ' : 'Disabilitato da ') . $owner, 'timestamp' => $timestamp, 'type' => $row['new'] ? 'warning' : ''];
                    } else {
                        $data_update = true;                        
                    }                    
                } 
                
                if($data_update){
                    $extra_content = [];
                    foreach($h->meta as $row){
                        $key = __('validation.attributes.' . $row['key']);
                        $extra_content[] = "$key da: '{$row['old']}' a '{$row['new']}'";
                    }
                    $activities[] = ['content' => 'Modificato da ' . $owner . ': ' . implode(' - ', $extra_content), 'timestamp' => $timestamp, 'type' => 'primary'];
                }
            }
        }
        
        return $activities;
        
        /*
        return [
            ['content' => 'sdas', 'timestamp' => '2023-01-12', 'type' => 'success'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'primary'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'warning'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'info'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'danger'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13'],
        ];
         * 
         */
        
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
